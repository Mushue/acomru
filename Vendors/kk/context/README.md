# KoolKode Context & Dependency Injection
Master: [![Build Status](https://travis-ci.org/koolkode/context.svg?branch=master)](https://travis-ci.org/koolkode/context)

Provides dependency injection and scoping inspired by Java's `CDI`, `Guice` and `Spring`. It relies on a DI container
that is being used to manage objects, their instantiation and dependencies. The container is configured using a
`ContainerBuilder` and `Bindings` providing a configuration DSL. Container configuration is primarily inspired
by `Guice`, it uses plain PHP code instead of external configuration files. Bindings use the fully-qualified name
of the bound type as key, you can lookup any object using a fully-qualified name. The DI container also supports
obtaining object instances that have not been bound, it will create such objects using their constructors and
auto-resolve all constructor params utilizing given type-hints.

## Container API
```php
<?php

namespace KoolKode\Context;

use KoolKode\Context\Bind\BindingInterface;

interface ContainerInterface
{
    public function get($typeName, InjectionPointInterface $point = NULL);

    public function getBinding($typeName);
   
    public function getBound(BindingInterface $binding, InjectionPointInterface $point = NULL);
    
    public function eachMarked(callable $callback);
}
```
The basic API for intercation with the DI container is outlined in `ContainerInterface`. In practice you will
be using `get()` and `getBound()` whenever you need to lookup an object instance using the container. Keep in
mind that using the container in this way is service location and should be avoided! Factory methods are an
exception to this rule as they are dealing with object construction and are somewhat coupled to the DI
container anyways. Still it is preferrable to use dependency injection as it is more explicit and easier to
spot dependencies.

Calling `get()` will check all registered bindings for a name match. If a match is found it will load the
binding and delegate to `getBound()` which will lookup and return the object instance. If no matching binding
is found the DI container will attempt to create a `ReflectionClass` for the given service name. If the type
exists and is instantiable (meaning it is not an interface, not a trait and not an abstract class) the container
will create a new instance of the target class and inject dependencies into the constructor. Setter injection
will not be performed as it requires a binding marked `SetterInjection`.

Bot `get()` and `getBound()` will throw a `ContextLookupException` if a requested service was not found or
could not be created due to some error or muissing dependency. In contrast to many other DI container implementations
the KoolKode container does not have a `has($typeName)` method that could be utilized for checking
availability of a certain service by name.

## Container Configuration
DI container configuration is split among `ContainerModules` that can be combined into a merged configuration.
Each container module ha a `build()` method that is responsible for setting up bindings using the given
`ContainerBuilder`. It is good practice to split container configuration into a set of modules for specific
tasks being performed by your application.

### Implementation Binding
```php
<?php

use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Scope\Singleton;

class MyContainerModule extends AbstractContainerModule
{
    public function build(ContainerBuilder $builder)
    {
        $builder->bind(MyServiceInterface::class)
                ->to(MyService::class);
        
        $builder->bind(MyService::class)
                ->scoped(new Singleton());
    }
}
```
The example binds `MyServiceInterface` to `MyService` that implements the interface and will be injected / returned
whenever an instance of the interface is requested. Binding interfaces allows for easy switching of implementations
and is considered a best practice.

The binding for `MyService` declares a scope of `singleton` that will ensure only 1 instance of the service is
created and re-used by all injections / lookups. Note that that in this case `to()` is not being called instructing
the DI container to actually create an instance of `MyService`. You could even leave out the binding for `MyService`
if you do not need scoping. Doing so would create a new instance of `MyService` every time it is injected.

### Inline Factory Binding
```php
<?php

use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;

class MyContainerModule extends AbstractContainerModule
{
    public function build(ContainerBuilder $builder)
    {
        $builder->bind(MyServiceInterface::class)
                ->to(function(MyConfigLoader $loader) {
                    return new MyService($loader->getConfig('some-key'));
                });
    }
}
```
The binding for `MyConfigInterface` is an inline factory binding, it will create an object instance using the
given closure whenever an instance of `MyConfigInterface` is being requested. The binding does not declare a scope
putting it in scope `dependent` which is the default scope being used by the DI container. The registered factory
function may declare an arbitrary number of arguments that will be resolved and passed by the DI container when
calling the factory. You can declare optional arguments by setting a default value for a parameter. The factory is
responsible for creating and returning an appropriate object instance ready for use.

### Factory Binding
```php
<?php

use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Scope\Singleton;
use Psr\Log\LoggerInterface;

class MyContainerModule extends AbstractContainerModule
{
    public function build(ContainerBuilder $builder)
    {
        $builder->bind(MyCache::class)
                ->scoped(new Singleton())
                ->to(MyCacheFactory::class, 'createCache');
    }
}

class MyCacheFactory
{
  protected $logger;
  
  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }
  
  public function createCache()
  {
    $cache = new MyCache();
    $cache->setLogger($this->logger);
    
    return $cache;
  }
}
```
`MyCache` is bound via a factory method declared by `MyCacheFactory`. This binding style is called a factory binding
and allows for arbitrary factories that are also resolved using the DI container. Custom factory classes can be
configured like any other type and make use of dependency injection as shown in the constructor of `MyCacheFactory`.
Any factory method may declare an arbitrary number of arguments which are resolved by the container in the same
way as for inline factories.

### Alias Binding
```php
<?php

use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Scope\Singleton;

class MyModule extends AbstractContainerModule
{
    public function build(ContainerBuilder $builder)
    {
        $builder->bind(MyServiceInterface::class)
                ->toAlias(MyService::class);
        
        $builder->bind(MyService::class)
                ->scoped(new Singleton());
    }
}
```
An alias binding is kind of a redirect to another binding. The DI container will not apply any scoping or inject
dependencies into the bound type. An alias binding has an implicit scope of `dependent` as the actual scope depends
on the bound type being aliased. The example binds `MyServiceInterface` using an alias to `MyService`. Whenever an
instance of `MyServiceInterface` is requested the DI container will lookup an instance of `MyService` and return it.
This behavior essentially re-uses the scope of the aliased binding.

### Markers
The DI container features a concept called `Marker` that provides the ability to enhance bindings with additional
metadata that can be queried using the container API.
```php
<?php

use KoolKode\Context\Bind\BindingInterface;
use KoolKode\Context\Bind\Marker;

final class Plugin extends Marker
{
    public $name;
    
    public function __construct($name)
    {
        $this->name = (string)$name;
    }
}

$builder->bind(MyPlugin::class)
        ->marked(new Plugin('my-plugin-name'));

$container = $builder->build();
$plugins = $container->eachMarked(function(Plugin $plugin, BindingInterface $binding) use($container) {
    return [$plugin->name => $container->getBound($binding)];
});
```
The example defines a new marker named `Plugin` that has a "name" property. You can attach markers to any binding
by calling `marked($marker)` on the binding. The DI container enables you to query all bindings that are marked
with a specific marker. In order to do so you habe to call `eachMarked()` and supply a callback that type-hints
the marker class on it's first argument. The second argument will receive the binding that has been marked. The
callback will be called once for each marker, if you mark a binding with multiple instances of the same marker
the callback will be invoked for every marker instance.

Marking bindings is a great way to retrieve a set of services for a specific purpose. Use cases include view
helpers, event listeners, strategies and any kind of extensions to other components. Custom markers must
inherit from `Marker` and may declare any number of fields and methods. Implementing a custom constructor is
optional but very convenient as it allows to create the marker and supply field values in one go.

### Initializers
```php
<?php

$builder->bind(MyService::class)
        ->initialize(function(MyService $service, MyConfigLoader $loader) {
            $service->setTitle($loader->getString('my-service-title'));
        });
```
Initializers can be applied to any binding. They are called after an object instance has been created and both
constructor and setter injection have been performed. Each initializer is a `Closure` that receives the created
object instance as first argument and may declare an arbitrary number of additional arguments that are resolved
by the DI container in the same way as in factories / inline factories.

The example illustrates an initializer for `MyService` that utilizes `MyConfigLoader` to set the title of the
created object instance. An alternative solution would be to use a factory / inline factory binding that
creates an instance of `MyService`, calls `setTitle()` and returns the object. Using a factory is more
verbose, especially if `MyService` has a number of dependencies that need to be injected.

### Decorators
```php
<?php

$builder->bind(MyService::class)
        ->decorate(function(MyService $service, MyCache $cache) {
            return new MyCachedService($service, $cache);
        }, 150);
```
Decorators feature the same behavior as initializers (they also receive the generated objet instance as first
argument and may declare any number of additional arguments resolved from the DI container) but they can replace
the object instance being generated by returning a different object.

All decorators are applied according to their priority which is an optional argument provided to the `decorate()`
method. The example specifies a priority of `150`. Decorators with higher priority are applied before decorators
with lower priority and receive the decorator as object instance argument.

Initializers are allways invoked before any decorator is applied to the object instance!

## Dependency Injection
KoolKode Context features 2 flavors of dependency injection: constructor injection and setter injection. The
preferred mechanism is constructor injection as it is very easy to setup and unit test plus you cannot forget
about dependencies when creating object instances.

### Constructor Injection
The DI container will automatically inject all arguments when invoking a constructor. It utilizes type-hints
to determine which object instance to inject into the target object. Constructor injection also works for types
that are not bound explicitly but are obtained from the container. Declaring default argument values allows for
optional dependencies that are not required to be available.
```php
class MyType
{
  public function __construct(ConnectionInterface $conn, LoggerInterface $logger = NULL)
  {
    // Go ahead and use them dependencies...
  }
}
```
The example illustrates a simple constructor injection as it can be used with the DI container without further
configuration. Keep in mind that you need to type-hint dependencies.

#### Resolvers
```php
<?php

use Psr\Log\LoggerInterface;

class MyGreeter
{
    public function __construct(LoggerInterface $logger, $greeting)
    {
        // Perform some initialization logic here... 
    }
}

$builder->bind(MyGreeter::class)
        ->resolve('greeting', 'Hello buddy');
```
Sometimes you find yourself in a situation where you need to pass parameters to a constrcutor that cannot be
resolved by the DI container. In the example `MyGreeter` requires a string defining the greeting to be used. As
there is no type-hint the container cannot auto-inject the dependency. The most generic solution to this problem
would be to create a factory or inline factory that creates an instance of `MyGreeter`. The factory now also
needs to express a dependency on `LoggerInterface` just for passing it to the constructor of `MyGreeter`. This
becomes tedious when there are more dependencies to be passed.

Resolvers offer a simple solution to populate specific constructor parameters by name. Unresolved params will be
populated by the DI container according to the default rules of constructor injection. In the example this leads
to `$logger` being injected by the container and `$greeting` being provided by the resolver. You can declare as
many resolvers as you like on a binding.

```php
<?php

use KoolKode\Config\Configuration;
use Psr\Log\LoggerInterface;

class MyGreeter
{
    public function __construct(LoggerInterface $logger, $greeting) { }
}

$builder->bind(MyGreeter::class)
        ->resolve('greeting', function(Configuration $config) {
            return 'Greeting of the day: ' . $config->getString('greeting', 'N/A');
        });
```
Resolvers are not limited to providing fixed values. You can use a `Closure` as resolver that will be invoked
by the container whenever the param in question needs to be populated. Closure-based resolvers may declare an
arbitrary number of arguments which are resolved by the DI container in the same way as factory / inline factory
arguments. This also holds true for `Configuration` objects, the injected config will be scoped to the bound
type which is `MyGreeter` in this example.

### Setter Injection
Setter injection uses mutator methods to inject dependencies into the target object **after** the constructor
hass been called. The `SetterInjection` marker enables setter injection for a binding. The marker provides two
styles of setter injection that can be enabled and even combined for any binding.

#### Convention-Based
```php
<?php

use KoolKode\Context\Bind\SetterInjection;

$builder->bind(MyService::class)
        ->marked(new SetterInjection(SetterInjection::CONVENTION));

class MyService
{
  public function injectOtherService(MyOtherService $service = NULL)
  {
    // Do something with the service...
  }
}
```
The example will enable convention-based setter injection for `MyService`. It will utilize any public instance
method that has a name starting with "inject" and declares at least one type-hinted parameter for injection. In
this case it will call `injectOtherService()` in order to inject an instance of `MyOtherService` obtained from
the DI container.

#### Filter-Based
```php
<?php

use KoolKode\Context\Bind\SetterBlacklist;
use KoolKode\Context\Bind\SetterInjection;

$builder->bind(MyService::class)
        ->marked(new SetterInjection(new SetterBlacklist('setFoo', 'Bar', 'setTransient*')));
        
$builder->bind(MyOtherService::class)
        ->marked(new SetterInjection())

class MyService
{
  public function setOtherService(MyOtherService $service = NULL)
  {
    // Do something with the service...
  }
}
```
Filter-based setter injection is the only style of setter injection that does not require the bound type to follow
conventions defined by KoolKode. The binding for `MyOtherService` given in the example will utilize any public
instance method that has a name starting with "set" for injection.

You can exclude methods being considered by passing a `SetterBlacklist` or restrict the set of methods being considered using
a `SetterWhitelist`. Both filters accept an arbitrary number of setter names / patterns. If such a name does not start
with "set" it will be prepended to the name as demonstrated by "Bar" which becomes "setBar". You can use an asterisk
to turn a name into a pattern, every asterisk in the pattern will match at least one character.

### Configuration
```php
<?php

use KoolKode\Config\Configuration;
use KoolKode\Context\Scope\Singleton;

$builder->bind(MyService::class)
        ->scoped(new Singleton())
        ->to(function(Configuration $config) {
            return new MyService($config->getString('title'));
        });

$config = new Configuration([
    'MyService' => [
        'title' => 'This is my service :)'
    ]
]);

$container = $builder->build();
$container->setConfiguration($config);
```
The DI container features special handling of `Configuration` object instances. Each DI container exposes a
`Configuration` (an empty config is created by default) that can be injected into bound types and factories. Injected
config objects are scoped to a bound type. In the example the bound type is `MyService`, the injected `Configuration`
contains settings found using a lookup key of "MyConfig". This will lead to an injected config containing a setting
called "title" with a value of "This is my service :)".
```php
<?php

use Foo\Bar\MyService;
use KoolKode\Config\Configuration;

$builder->bind(MyService::class)
        ->to(function(Configuration $config) {
            return new MyService($config->getString('title'));
        });

$config = new Configuration([
    'Foo' => [
        'Bar' => [
            'MyService' => [
                'title' => 'This is my service :)'
            ]
        ]
    ]
]);

$container = $builder->build();
$container->setConfiguration($config);
```
The second example illustrates how namespaces affect the lookup of config settings. Each namespace results in an
additional nested array in the config data. The given example results in the same configuration injection being
peformed as in the first example.

### Injection Points
```php
<?php

use KoolKode\Context\InjectionPointInterface;

$builder->bind(MyService::class)
        ->to(function(MyConfigLoader $loader, InjectionPointInterface $point = NULL) {
            if($point === NULL) {
                return new MyService();
            }
            return new MyService($loader->getConfig($point->getTypeName()));
        });
```
Injection points expose information about the target of an injection. They can be used with inline factories
and factories in order to produce object instances specific to the target of an injection. The example shows
a use case for this: the factory method will use an `InjectionPoint` to lookup configuration settings for
`MyService` according to the target type of the injection. Another good use case is injection of loggers where
the factory can set an appropriate logging channel or level for the injected logger based on the target of
the injection.

Injection Points may only be used when the binding declares a scope of `dependent`!

## Scopes
The DI container features scopes in a way that resembles Java's `CDI` implementation. Scopes are used to manage
the lifecycle of bound objects. KoolKode Context features a set of pre-defined scopes that are usable in any
application. Custom scopes can be created in order to implement scopes tailored to specific requirement.

### Dependent
This is the default scope of all bindings (and all types that are created without a binding being registered).
Dependent objects are not really managed by the container, they are just created and returned to the calling code.
The container provides both constructor and setter injection into dependent objects.

### Singleton
The singleton scope allows for shared object instances that created once and re-used whenever they are needed
afterwards. This scope behaves pretty similar to implementing the well-known singleton design pattern but without
the need for static access to a shared object instance. A binding can be configured to use the Singleton scope by
calling `$binding->scoped(new Singleton())`, the object instance is (lazily) created as it is first accessed. You
need to import the scope like this: `use KoolKode\Context\Scope\Singleton;`

An alternative way to register a singleton is to call `bindInstance($typeName, $object)` on the DI container which
will register the given object instance under the given name. The second approach is especially useful during unit
testing as it allows for adding specific instances to a container without having to register them in the `ContainerBuilder`.

### ApplicationScoped
The application scope resembles the Singleton scope in that it allows for sharing an object instance. There is a
very important difference though: A proxy class is created for each bound type that is returned instead of an
actual object instance. The created proxy class (called **scoped proxy**) extends (or implements) the type of the
binding (**not** the bound type!).
```php
<?php

use KoolKode\Context\Scope\ApplicationScoped;

interface FooInterface { }

class Foo implements FooInterface { }

$builder->bind(FooInterface::class)->to(Foo::class)->scoped(new ApplicationScoped());
```
The example will create a scoped proxy called `FooInterface__scoped` that implements `FooInterface`. When any method
is called on the proxy it will load the actual object instance from the container and delegate the method call. The
proxy pattern allows for injection of object instances without actually loading the real object. This can be a useful
way to improve performance because it allows for lazy-loaded database connections etc.

There are some (more or less) obvious pifalls related to scoped proxies:

- Scoped proxies can be generated when binding interfaces, in this case you **must** type-hint against that interface,
  a type-hint against the implementing class will not work! Consider the previous example: You need to type-hint at
  `FooInterface` when injecting the bound object, a type-hint of `Foo` will fail since the generated proxy implements
  `FooInterface` but it does not extend from `Foo`.
  
- Generation of scoped proxies from non-abstract classes works only for non-final classes.

- Generation of scoped proxies from classes is only possible when the class contains no **final** public methods
  (including constructors). Generating a proxy from an abstract class is possible and constrained by the same
  limitations as generation of a proxy from a non-abstract class.

- The generation of proxy classess is configured via bindings. Whenever you are receivig an injected dependency you
  do not now in advance if you are dealing with a scoped proxy. While this usually is not a big problem there are some
  edge cases where you need to make sure you are dealing with the real object instead of a proxy. You can call
  `Scope::unwrap($object)` using any object in order to obtain the unproxied object instance. Kepp in mind that doing
  so will trigger loading of the actual object instance and breaks scope management using the container. Use at your
  own risk...

- If you need to check for a scoped proxy you should use instanceof and check for `ScopedProxyInterface`. This will
  tell you if you are dealing with a scoped proxy without activating the proxied object instance.
