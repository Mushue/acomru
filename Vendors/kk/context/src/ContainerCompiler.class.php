<?php

/*
 * This file is part of KoolKode Context and Dependency Injection.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\Context;

use KoolKode\Config\Configuration;
use KoolKode\Context\Bind\Binding;
use KoolKode\Context\Bind\BindingInterface;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Bind\DelegateBinding;
use KoolKode\Context\Bind\SetterInjection;
use KoolKode\Context\Scope\ScopedProxyGenerator;
use KoolKode\Util\NamespaceContext;
use KoolKode\Util\ReflectionTrait;
use KoolKode\Util\Filesystem;

/**
 * Compiles a DI container builder into an optimized DI container using plain PHP.
 *
 * @author Martin Schröder
 */
class ContainerCompiler
{
    use ReflectionTrait;

    protected $typeName;

    protected $namespace;

    protected $methods = [];

    protected $namespaceContextCache = [];

    protected $proxyGenerator;

    protected $manager;

    /**
     * Create a new DI container compiler.
     *
     * @param string $typeName
     */
    public function __construct($typeName = 'KoolKode\Context\Compiled\CompiledContainerImpl')
    {
        $parts = explode('\\', $typeName);

        $this->typeName = (string)array_pop($parts);
        $this->namespace = implode('\\', $parts);

        $this->proxyGenerator = new ScopedProxyGenerator();
    }

    /**
     * Generate PHP code of an optimized DI container from the given builder.
     *
     * @param ContainerBuilder $builder
     * @param string $proxyCachePath Cache directory for scoped proxies, NULL deactivates pre-generation of proxies.
     * @param array <string> $additionalProxies Additional type names that need a scoped proxy.
     * @return string
     */
    public function compile(ContainerBuilder $builder, $proxyCachePath = NULL, array $additionalProxies = [])
    {
        try {
            return $this->compileBuilder($builder, $proxyCachePath, $additionalProxies);
        } finally {
            $this->methods = [];
            $this->namespaceContextCache = [];
        }
    }

    /**
     * Compiles the given container builder and takes care of scoped proxy generation.
     *
     * @param ContainerBuilder $builder
     * @param string $proxyCachePath
     * @param array <string> $additionalProxies
     * @return string
     */
    protected function compileBuilder(ContainerBuilder $builder, $proxyCachePath = NULL, array $additionalProxies = [])
    {
        $imports = [
            DelegateBinding::class,
            ContextLookupException::class,
            InjectionPoint::class,
            InjectionPointInterface::class
        ];

        $code = '<?php' . "\n\n";
        $code .= 'namespace ' . $this->namespace . " {\n";

        foreach ($imports as $typeName) {
            $code .= "\nuse " . ltrim($typeName, '\\') . ";";
        }

        if (!empty($imports)) {
            $code .= "\n";
        }

        $code .= "\nfinal class {$this->typeName} extends \KoolKode\Context\CompiledContainer {\n\n";

        $bindings = $builder->getBindings();
        $marked = [];

        // Write bound properties:

        $bound = [];

        foreach ($bindings as $binding) {
            $bound[$binding->getTypeName()] = true;
        }

        $code .= "\tprotected \$bound = " . var_export($bound, true) . ";\n";

        foreach ($bindings as $binding) {
            foreach ($binding->getMarkers() as $marker) {
                $marked[get_class($marker)][(string)$binding] = $binding;
            }

            $code .= $this->compileBinding($binding);
        }

        $code .= $this->compileMarkerLookup($marked);

        foreach ($this->methods as $method) {
            $code .= "\n\n" . $method . "\n";
        }

        if ($proxyCachePath !== NULL) {
            $code .= $this->compileScopedProxies($builder, $proxyCachePath, $additionalProxies);
        }

        $code .= "}\n\n";
        $code .= "}\n\n";

        return $code;
    }

    /**
     * Compiles PHP code for the given binding.
     *
     * @param Binding $binding
     * @return string
     */
    protected function compileBinding(Binding $binding)
    {
        $prop = str_replace('\\', '_', $binding->getTypeName());

        $code = "\tpublic function binding_" . $prop . "() {\n\n";

        $code .= "\t\t\t\$binding = new DelegateBinding(" . var_export($binding->getTypeName(), true);
        $code .= ', ' . var_export($binding->getScope(), true);
        $code .= ', ' . var_export($binding->getOptions(), true) . ', function(InjectionPointInterface $point = NULL) {' . "\n";

        switch ($binding->getOptions() & BindingInterface::MASK_TYPE) {
            case BindingInterface::TYPE_ALIAS:
                $code .= "return \$this->get(" . var_export($binding->getTarget(), true) . ", \$point);\n";
                break;
            case BindingInterface::TYPE_IMPLEMENTATION:
                $code .= $this->compileImplementationBinding($binding);
                break;
            case BindingInterface::TYPE_FACTORY_ALIAS:
                $code .= $this->compileFactoryBinding($binding);
                break;
            case BindingInterface::TYPE_FACTORY:
                $code .= $this->compileInlineFactoryBinding($binding);
        }

        $code .= "});\n";
        $code .= "\t\treturn \$binding;\n";
        $code .= "\t}\n\n";

        return $code;
    }

    /**
     * Compiles an implementation binding using a call to createObject() on the container.
     *
     * @param Binding $binding
     * @return string
     */
    protected function compileImplementationBinding(Binding $binding)
    {
        $code = "return \$this->createObject(";
        $code .= var_export($binding->getTarget(), true) . ', [';
        $rpos = 0;

        foreach ((array)$binding->getResolvers() as $para => $resolver) {
            if ($rpos++ > 0) {
                $code .= ', ';
            }

            if ($resolver instanceof \Closure) {
                $num = count($this->methods);

                $ref = new \ReflectionFunction($resolver);
                $sig = $this->generateReplacementMethodSignature($ref, $num);
                $body = $this->extractClosureCode($ref);

                $this->methods[] = "\t\t" . $sig . " {\n" . $body . "\n\t\t}";

                $call = $this->generateCallCode($binding->getTypeName(), $ref, $num);

                $code .= var_export($para, true) . ' => function(InjectionPointInterface $point = NULL) { return ' . $call . '; }';
            } else {
                $code .= var_export($para, true) . ' => ' . var_export($resolver, true);
            }
        }

        $code .= "], [";
        $rpos = 0;

        foreach ((array)$binding->getInitializers() as $initializer) {
            if ($rpos++ > 0) {
                $code .= ', ';
            }

            $num = count($this->methods);

            $ref = new \ReflectionFunction($initializer);
            $sig = $this->generateReplacementMethodSignature($ref, $num);
            $body = $this->extractClosureCode($ref);

            $code .= '[$this, ' . var_export('call_' . $num, true) . ']';

            $this->methods[] = "\t\t" . $sig . " {\n" . $body . "\n\t\t}";
        }

        $code .= "], " . $this->compileSetterInjector($binding) . ");\n";
        $code .= ";\n";

        return $code;
    }

    /**
     * Generate a signature for a method replacing the given closure.
     *
     * @param \ReflectionFunction $ref
     * @param integer $num
     * @return string
     */
    protected function generateReplacementMethodSignature(\ReflectionFunction $ref, $num)
    {
        $code = 'protected function call_' . $num . '(';

        foreach ($ref->getParameters() as $i => $param) {
            if ($i != 0) {
                $code .= ', ';
            }

            if (NULL !== ($ptype = $this->getParamType($param))) {
                $code .= '\\' . $ptype . ' ';
            }

            if ($param->isPassedByReference()) {
                $code .= '& ';
            }

            $code .= '$' . $param->name;

            if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                $code .= ' = ' . var_export($param->getDefaultValue(), true);
            }
        }

        return $code . ')';
    }

    /**
     * Extract code of the given closure and process it for use in a replacement method.
     *
     * @param \ReflectionFunction $ref
     * @return string
     */
    protected function extractClosureCode(\ReflectionFunction $ref)
    {
        $file = $ref->getFileName();
        $lines = file($file, FILE_IGNORE_NEW_LINES);

        if (isset($this->namespaceContextCache[$file])) {
            $namespaceContext = $this->namespaceContextCache[$file];
        } else {
            $tokens = token_get_all(implode("\n", $lines));
            $namespaceContext = $this->namespaceContextCache[$file] = $this->generateNamespaceContext($tokens);
        }

        // Extract closure code from file source.
        $funcLines = array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1);
        $snippet = implode("\n", array_map('trim', $funcLines));
        $snippet = preg_replace("'^.*\{'", '', $snippet);
        $snippet = preg_replace("'\}.*$'", '', $snippet);

        // Parse closure code snippet and expand unqualified names.
        $tokens = array_slice(token_get_all('<?php ' . $snippet), 1);
        $buffer = $this->expandNames($tokens, $namespaceContext);

        return str_replace("\n", "\n\t\t", "\n" . trim($buffer));
    }

    /**
     * Read namespace context from the given PHP source tokens.
     *
     * @param array $tokens
     * @return NamespaceContext
     */
    protected function generateNamespaceContext(array & $tokens)
    {
        $namespaceContext = new NamespaceContext();

        for ($size = count($tokens), $i = 0; $i < $size; $i++) {
            if (is_array($tokens[$i])) {
                switch ($tokens[$i][0]) {
                    case T_NAMESPACE:

                        $namespace = '';

                        for ($i++; $i < $size; $i++) {
                            if (is_array($tokens[$i])) {
                                switch ($tokens[$i][0]) {
                                    case T_WHITESPACE:
                                    case T_COMMENT:
                                    case T_DOC_COMMENT:
                                        continue 2;
                                    case T_NS_SEPARATOR:
                                    case T_STRING:
                                        $namespace .= $tokens[$i][1];
                                        continue 2;
                                }
                            }

                            break;
                        }

                        $namespaceContext = new NamespaceContext($namespace);

                        break;
                    case T_USE:
                        do {
                            $i++;
                            $this->readImport($tokens, $i, $namespaceContext);
                        } while ($tokens[$i] == ',');

                        break;
                    case T_CLASS:
                    case T_INTERFACE:
                    case T_TRAIT:
                        // Abort namespace detection as soon as code declaring a type is reached.
                        break 2;
                }
            }
        }

        return $namespaceContext;
    }

    /**
     * Read a PHP import statement and register it with the given namespace context.
     *
     * @param array $tokens
     * @param integer $i
     * @param NamespaceContext $context
     */
    protected function readImport(array $tokens, & $i, NamespaceContext $context)
    {
        $import = '';

        for ($size = count($tokens); $i < $size; $i++) {
            if (is_array($tokens[$i])) {
                switch ($tokens[$i][0]) {
                    case T_WHITESPACE:
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        continue 2;
                    case T_NS_SEPARATOR:
                    case T_STRING:
                        $import .= $tokens[$i][1];
                        continue 2;
                }
            }

            break;
        }

        if (is_array($tokens[$i]) && $tokens[$i][0] == T_AS) {
            $i++;
            $alias = '';

            for ($size = count($tokens); $i < $size; $i++) {
                if (is_array($tokens[$i])) {
                    switch ($tokens[$i][0]) {
                        case T_WHITESPACE:
                        case T_COMMENT:
                        case T_DOC_COMMENT:
                            continue 2;
                        case T_STRING:
                            $alias .= $tokens[$i][1];
                            continue 2;
                    }
                }

                break;
            }

            $context->addAliasedImport($alias, $import);
        } else {
            $context->addImport($import);
        }
    }

    /**
     * Expand all unqualified names using the given namespace context in the given source code tokens.
     *
     * @param array $tokens
     * @param NamespaceContext $context
     * @return string
     */
    protected function expandNames(array & $tokens, NamespaceContext $context)
    {
        // Expand name if one of these tokens is found before the name.
        static $beforeTokens = [
            T_NEW, // Object instantiation
            T_INSTANCEOF // Instanceof check
        ];

        // Expand name if one of these tokens is found after the name
        static $afterTokens = [
            T_DOUBLE_COLON, // Static access or class constant lookup
            T_VARIABLE // Type-hinted function / method argument
        ];

        $count = count($tokens) - 1;
        $buffer = '';

        for ($size = count($tokens), $i = 0; $i < $size; $i++) {
            if (is_array($tokens[$i]) && ($tokens[$i][0] == T_STRING || $tokens[$i][0] == T_NS_SEPARATOR)) {
                $pos = $i;
                $before = $pos - 1;
                $after = $pos + 1;
                $name = $tokens[$i][1];

                $i++;

                // Read name with namespace parts if needed.
                for (; $i < $size; $i++) {
                    if (is_array($tokens[$i])) {
                        switch ($tokens[$i][0]) {
                            case T_WHITESPACE:
                            case T_COMMENT:
                            case T_DOC_COMMENT:
                                continue 2;
                            case T_NS_SEPARATOR:
                            case T_STRING:
                                $name .= $tokens[$i][1];
                                continue 2;
                        }
                    }

                    break;
                }

                // Read first significant token before the name.
                $prev = NULL;
                while ($before >= 0) {
                    $prev = $tokens[$before];

                    if (is_array($prev)) {
                        switch ($prev[0]) {
                            case T_WHITESPACE:
                            case T_COMMENT:
                            case T_DOC_COMMENT:
                                $before--;
                                continue 2;
                        }
                    }

                    break;
                }

                // Read first significant token after the name.
                $next = NULL;
                while ($after < $count) {
                    $next = $tokens[$after];

                    if (is_array($next)) {
                        switch ($next[0]) {
                            case T_WHITESPACE:
                            case T_COMMENT:
                            case T_DOC_COMMENT:
                                $after++;
                                continue 2;
                        }
                    }

                    break;
                }

                // Expand names as needed.
                $expand = false;

                if (is_array($prev) && in_array($prev[0], $beforeTokens)) {
                    $expand = true;
                }

                if (is_array($next) && in_array($next[0], $afterTokens)) {
                    $expand = true;
                }

                if ($expand) {
                    $buffer .= '\\' . ltrim($context->lookup($name), '\\');
                } else {
                    $buffer .= $name;
                }
            }

            if (is_array($tokens[$i])) {
                $buffer .= $tokens[$i][1];
            } else {
                $buffer .= $tokens[$i];
            }
        }

        return $buffer;
    }

    /**
     * Generate code calling a replacement method with arguments.
     *
     * @param string $typeName
     * @param \ReflectionFunction $ref
     * @param integer $num
     * @param array <string> $prepend
     * @return string
     *
     * @throws \RuntimeException When a param value could not be populated.
     */
    protected function generateCallCode($typeName, \ReflectionFunction $ref, $num, array $prepend = [], array $resolvers = NULL)
    {
        $code = '$this->call_' . $num . '(';

        foreach ($ref->getParameters() as $i => $param) {
            if ($i != 0) {
                $code .= ', ';
            }

            if (array_key_exists($i, $prepend)) {
                $code .= $prepend[$i];

                continue;
            }

            if ($resolvers !== NULL && array_key_exists($param->name, $resolvers)) {
                $resolver = $resolvers[$param->name];

                if ($resolver instanceof \Closure) {
                    $num = count($this->methods);

                    $ref = new \ReflectionFunction($resolver);
                    $sig = $this->generateReplacementMethodSignature($ref, $num);
                    $body = $this->extractClosureCode($ref);

                    $this->methods[] = "\t\t" . $sig . " {\n" . $body . "\n\t\t}";

                    $code .= $this->generateCallCode($typeName, $ref, $num);

                    continue;
                } elseif ($resolver instanceof CompiledCodeFragment) {
                    $code .= $resolver;

                    continue;
                }

                $code .= var_export($resolver, true);

                continue;
            }

            if (NULL !== ($ptype = $this->getParamType($param))) {
                if ($ptype === InjectionPointInterface::class) {
                    $code .= '$point';

                    continue;
                }

                $code .= '$this->get(' . var_export($ptype, true) . ', isset($point) ? $point : NULL)';

                continue;
            }

            if ($param->isOptional()) {
                $code .= $param->isDefaultValueAvailable() ? var_export($param->getDefaultValue(), true) : 'NULL';

                continue;
            }

            throw new TypeNotFoundException(sprintf('Cannot populate closure param "%s" without type hint or resolver', $param->name));
        }

        return $code . ')';
    }

    /**
     * Compile a setter injector for a binding.
     *
     * @param Binding $binding
     * @param boolean $wrapClosure
     * @return string
     */
    protected function compileSetterInjector(Binding $binding, $wrapClosure = true)
    {
        $setters = $binding->getMarkers(SetterInjection::class);

        if (empty($setters)) {
            return 'NULL';
        }

        $setterTypeName = $binding->isImplementationBinding() ? $binding->getTarget() : $binding->getTypeName();

        $code = $wrapClosure ? 'function($obj) { ' : '';
        $ref = new \ReflectionClass($setterTypeName);

        foreach ($setters as $setter) {
            foreach ($this->collectSetterMethods($ref, $setter) as $method) {
                $params = $method->getParameters();

                if (empty($params)) {
                    continue;
                }

                $injector = '$obj->' . $method->getName() . '(';

                foreach ($params as $i => $param) {
                    if (NULL === ($paramTypeName = $param->getClass())) {
                        continue 2;
                    }

                    if ($i != 0) {
                        $injector .= ', ';
                    }

                    $nullable = $param->isOptional() ? 'Nullable' : '';

                    $injector .= '$this->get' . $nullable . '(' . var_export($paramTypeName->name, true) . ', new InjectionPoint(';
                    $injector .= 'get_class($obj), ' . var_export($method->getName(), true) . '))';
                }

                $code .= $injector . '); ';
            }
        }

        return $wrapClosure ? ($code . '}') : $code;
    }

    /**
     * Collect all matching setter injetion methods for the given type.
     *
     * @param \ReflectionClass $ref
     * @param SetterInjection $setter
     * @return array<string, MethodInfoInterface>
     */
    protected function collectSetterMethods(\ReflectionClass $ref, SetterInjection $setter)
    {
        $methods = [];

        foreach ($ref->getMethods() as $method) {
            if ($method->isStatic() || $method->isAbstract() || !$method->isPublic()) {
                continue;
            }

            if ($setter->accept($method)) {
                $methods[strtolower($method->getName())] = $method;
            }
        }

        return $methods;
    }

    /**
     * Compiles a factory binding using a call to callFactoryMethod() on the container.
     *
     * @param Binding $binding
     * @return string
     */
    protected function compileFactoryBinding(Binding $binding)
    {
        $code = '';
        $target = $binding->getTarget();

        $call = '$this->callFactoryMethod(' . var_export($binding->getTypeName(), true);
        $call .= ', $this->get(' . var_export($target[0], true);
        $call .= '), ' . var_export($target[1], true);

        $resolvers = (array)$binding->getResolvers();

        if (empty($resolvers)) {
            $call .= ', NULL';
        } else {
            $call .= ', [';
            $i = 0;

            foreach ($resolvers as $name => $resolver) {
                if ($i++ != 0) {
                    $call .= ', ';
                }

                $call .= var_export($name, true) . ' => ';

                if ($resolver instanceof \Closure) {
                    $num = count($this->methods);

                    $ref = new \ReflectionFunction($resolver);
                    $sig = $this->generateReplacementMethodSignature($ref, $num);
                    $body = $this->extractClosureCode($ref);

                    $this->methods[] = "\t\t" . $sig . " {\n" . $body . "\n\t\t}";

                    $call = $this->generateCallCode($binding->getTypeName(), $ref, $num);

                    $call .= 'function() { return ' . $call . '; }';

                    continue;
                }

                $call .= var_export($resolver, true);
            }

            $call .= ']';
        }

        $call .= ', $point)';

        $setterInjection = $binding->isMarked(SetterInjection::class);
        $initializers = (array)$binding->getInitializers();

        if (empty($initializers) && !$setterInjection) {
            $code .= "return \$this->initialize(" . $call . ");\n";
        } else {
            $code .= "\$obj = \$this->initialize(" . $call . ");\n";

            if ($setterInjection) {
                $code .= "\t\t" . $this->compileSetterInjector($binding, false) . "\n";
            }

            foreach ($initializers as $initializer) {
                $num = count($this->methods);

                $ref = new \ReflectionFunction($initializer);
                $sig = $this->generateReplacementMethodSignature($ref, $num);
                $body = $this->extractClosureCode($ref);

                $this->methods[] = "\t\t" . $sig . " {\n" . $body . "\n\t\t}";

                $code .= "\t\t\$obj = " . $this->generateCallCode($binding->getTypeName(), $ref, $num, ['$obj']) . " ?: \$obj;\n";
            }

            $code .= "\t\treturn \$obj;\n";
        }

        return $code;
    }

    /**
     * Compiles an inline factory binding by creating a replacement method in the DI container.
     *
     * @param Binding $binding
     * @return string
     */
    protected function compileInlineFactoryBinding(Binding $binding)
    {
        $code = '';
        $num = count($this->methods);

        $ref = new \ReflectionFunction($binding->getTarget());
        $sig = $this->generateReplacementMethodSignature($ref, $num);
        $body = $this->extractClosureCode($ref);

        $this->methods[] = "\t\t" . $sig . " {\n" . $body . "\n\t\t}";

        $resolvers = (array)$binding->getResolvers();

        foreach ($ref->getParameters() as $param) {
            if ($this->getParamType($param) === Configuration::class) {
                $con = "\$this->config->getConfig(" . var_export(str_replace('\\', '.', $binding->getTypeName()), true) . ')';

                $resolvers[$param->name] = new CompiledCodeFragment($con);
            }
        }

        foreach ($ref->getParameters() as $param) {
            if (!$param->isOptional() && InjectionPointInterface::class === $this->getParamType($param)) {
                $code .= "\t\tif(\$point === NULL) {\n";
                $code .= "\t\t\tthrow new ContextLookupException(";
                $code .= var_export(sprintf('Factory for %s requires access to an injection point', $binding->getTypeName()), true);
                $code .= ");\n";
                $code .= "\t\t}\n";
            }
        }

        $call = $this->generateCallCode($binding->getTypeName(), $ref, $num, [], $resolvers);

        $setterInjection = $binding->isMarked(SetterInjection::class);
        $initializers = (array)$binding->getInitializers();

        if (empty($initializers) && !$setterInjection) {
            $code .= "\t\treturn \$this->initialize(" . $call . ");\n";
        } else {
            $code .= "\t\t\$obj = \$this->initialize(" . $call . ");\n";

            if ($setterInjection) {
                $code .= "\t\t" . $this->compileSetterInjector($binding, false) . "\n";
            }

            foreach ($initializers as $initializer) {
                $num = count($this->methods);

                $ref = new \ReflectionFunction($initializer);
                $sig = $this->generateReplacementMethodSignature($ref, $num);
                $body = $this->extractClosureCode($ref);

                $this->methods[] = "\t\t" . $sig . " {\n" . $body . "\n\t\t}";

                $code .= "\t\t\$obj = " . $this->generateCallCode($binding->getTypeName(), $ref, $num, ['$obj']) . " ?: \$obj;\n";
            }

            $code .= "\t\treturn \$obj;\n";
        }

        return $code;
    }

    /**
     * Compiles lookup methods for marked bindings.
     *
     * @param array <string, array<string, Binding>> $marked
     * @return string
     */
    protected function compileMarkerLookup(array $marked)
    {
        $code = '';

        foreach ($marked as $markerType => $markedBindings) {
            $code .= "\tpublic function markedBindings_" . str_replace('\\', '_', $markerType) . "() {\n";
            $code .= "\t\tstatic \$bindings;\n";
            $code .= "\t\tif(\$bindings === NULL) {\n";
            $code .= "\t\t\t\$bindings = [\n";

            $index = 0;

            foreach ($markedBindings as $binding) {
                foreach ($binding->getMarkers() as $marker) {
                    if (!$marker instanceof $markerType) {
                        continue;
                    }

                    if ($index++ != 0) {
                        $code .= ",\n";
                    }

                    $vars = get_object_vars($marker);

                    $code .= "\t\t\t\t[\$this->reviveMarker(";
                    $code .= var_export(get_class($marker), true);

                    if (!empty($vars)) {
                        $code .= ', ' . var_export($vars, true);
                    }

                    $prop = str_replace('\\', '_', $binding->getTypeName());

                    $code .= "), (\$this->bound[" . var_export($binding->getTypeName(), true);
                    $code .= "] !== true) ? \$this->bound[" . var_export($binding->getTypeName(), true);
                    $code .= "] : (\$this->bound[" . var_export($binding->getTypeName(), true);
                    $code .= "] = \$this->binding_$prop())]";
                }
            }

            $code .= "\n\t\t\t];\n\t\t}\n";
            $code .= "\t\treturn \$bindings;\n";
            $code .= "\t}\n\n";
        }

        return $code;
    }

    /**
     * Compiles scoped proxy types and saves them on the filesystem.
     *
     * @param ContainerBuilder $builder
     * @param string $proxyCachePath
     * @param array <stribg> $additionalProxies
     * @return string
     */
    protected function compileScopedProxies(ContainerBuilder $builder, $proxyCachePath, array $additionalProxies = [])
    {
        $code = "\t\tpublic function loadScopedProxy(\$typeName) {\n";
        $code .= "\t\tswitch(\$typeName) {\n";

        $proxyTypes = [];

        foreach ($builder->getProxyBindings() as $binding) {
            $ref = new \ReflectionClass($binding->getTypeName());
            $proxyTypes[$ref->name] = $ref;
        }

        foreach ($additionalProxies as $add) {
            $ref = new \ReflectionClass($add);
            $proxyTypes[$ref->name] = $ref;
        }

        foreach ($proxyTypes as $ref) {
            $parent = $ref;
            $mtime = filemtime($ref->getFileName());

            while ($parent = $ref->getParentClass()) {
                $mtime = max($mtime, filemtime($parent->getFileName()));
            }

            $file = $proxyCachePath . '/' . md5(strtolower($ref->name)) . '.php';
            $create = !is_file($file) || filemtime($file) < $mtime;

            if ($create) {
                $proxyCode = '<?php' . "\n\n" . $this->proxyGenerator->generateProxyCode($ref);

                Filesystem::writeFile($file, $proxyCode);
            }

            $code .= "\t\t\tcase " . var_export($ref->name, true) . ":\n";
            $code .= "\t\t\t\trequire_once " . var_export($file, true) . ";\n";
            $code .= "\t\t\t\treturn \$typeName . '__scoped';\n";
            $code .= "\t\t\t\tbreak;\n";
        }

        $code .= "\t\t}\n";
        $code .= "\t\treturn parent::loadScopedProxy(\$typeName);\n";
        $code .= "\t\t}\n\n";

        return $code;
    }
}
