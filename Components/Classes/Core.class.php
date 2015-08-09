<?php

/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 09.08.2015
 * Time: 13:05
 */
class Core implements CoreInterface
{
    /**
     * @var \SplStack
     */
    protected static $containerStack;
    protected $contextName;
    protected $directory;
    protected $projectDirectory;
    protected $cacheDirectory;
    protected $config;
    protected $eventDispatcher;
    protected $container;
    protected $loader;
    private $booted = false;
    private $komponents;
    private $instrumentors;
    private $cache;
    private $env;
    private $instrumentationProcessor;

    public function __construct($contextName = 'development')
    {
        if (static::$containerStack === NULL) {
            static::$containerStack = new \SplStack();
        }

        $this->contextName = (string)$contextName;
        $this->directory = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $this->setupRootDirectory()), '/\\');
        $this->cacheDirectory = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $this->setupCacheDirectory()), '/\\');

        if ($this->projectDirectory === NULL) {
            $this->projectDirectory = PATH_BASE;
        }

        $this->cache = $this->createCoreCache();
        $this->initialize();
    }

    /**
     * @return string
     */
    protected function setupRootDirectory()
    {
        $ref = new \ReflectionClass(get_class($this));
        return dirname(dirname($ref->getFileName()));
    }

    /**
     * @return string
     */
    protected function setupCacheDirectory()
    {
        return PATH_CACHE_DATA;
    }

    protected function createCoreCache()
    {
        return new CoreCache($this);
    }

    protected function initialize()
    {
    }

    public static function getContainer()
    {
        return static::$containerStack->top();
    }

    public static function get($typeName, \KoolKode\Context\InjectionPointInterface $point = NULL)
    {

        return static::$containerStack->top()->get($typeName, $point);
    }

    public function isInstrumentationEnabled()
    {
        return false;
    }

    public function getContextName()
    {
        return $this->contextName;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function getProjectDirectory()
    {
        return $this->projectDirectory;
    }

    public function getCacheDirectory()
    {
        return $this->cacheDirectory;
    }

    public function getMigrationDirectory()
    {
        return $this->directory . DIRECTORY_SEPARATOR . 'migration';
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function getEnvironment()
    {
        if ($this->env === NULL) {
            $this->env = 'development';
        }

        return $this->env;
    }

    public function getManifest()
    {
        // TODO: Implement getManifest() method.
    }

    public function getInstrumentors()
    {
        // TODO: Implement getInstrumentors() method.
    }

    public function getResource($resource)
    {
        // TODO: Implement getResource() method.
    }

    public function loadContainerModules(\KoolKode\Context\Bind\ContainerModuleLoader $loader)
    {
        // TODO: Implement loadContainerModules() method.
    }

    public function run($terminate = true)
    {
        Session::start();

        $this->bootCore();

        /** @var WebKernel $core */
        $core = $this->container->get(WebKernel::class);
        $request = RouterRewrite::me()->route(HttpRequest::createFromGlobals());

        $core
            ->dropVar(WebKernel::OBJ_REQUEST)
            ->setRequest($request)
            ->setPathWeb(PATH_WEB)
            ->setPathController(PATH_CONTROLLERS)
            ->setPathTemplate(PATH_VIEWS)
            ->setPathTemplateDefault(PATH_VIEWS)
            ->setServiceLocator($this->container->get(ServiceLocator::class))
            ->add(WebKernelBufferHandler::create())
            ->add(WebKernelSessionHandler::create()
                ->setCookieDomain(COOKIE_HOST_NAME)
                ->setSessionName('acomru')
            )
            ->add(WebKernelAjaxHandler::create())
            ->add(WebKernelControllerResolverHandler::create())
            ->add(WebKernelControllerHandler::create())
            ->add(WebKernelViewHandler::create());

        $this->invokeBoot($this->loader);

        $core->run();
    }

    public function bootCore()
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;
        $this->config = $this->loadConfiguration();

        $loader = new \KoolKode\Context\Bind\ContainerModuleLoader();
        $this->loadDefaultContainerModules($loader);

        $scopes = new \KoolKode\Context\Scope\ScopeLoader();

        foreach ($loader as $module) {
            if ($module instanceof \KoolKode\Context\Scope\ScopeProviderInterface) {
                $module->loadScopes($scopes);
            }
        }
        $this->syncCoreCache($loader, $scopes);

        $this->container = $this->createContainer($loader, $scopes);
        $this->container->setConfiguration($this->config);
        $this->container->bindInstance(CoreInterface::class, $this);

        static::$containerStack->push($this->container);

        foreach ($scopes as $scope) {
            $this->container->registerScope($scope);
        }

        $this->loader = $loader;
    }

    protected function loadConfiguration()
    {
        return $this->cache->loadConfiguration();
    }

    protected function loadDefaultContainerModules(\KoolKode\Context\Bind\ContainerModuleLoader $loader)
    {
        $loader
            ->registerModule(new \Modules\Core\CoreModule());

        $configLoader = new \KoolKode\Config\ConfigurationLoader();
        $configLoader->registerLoader(new \KoolKode\Config\PhpConfigurationLoader());

        $file = new \SplFileInfo(PATH_MODULES . 'modules.config.php');
        $source = new \KoolKode\Config\ConfigurationSource($file);

        $this->config = $source->loadConfiguration($configLoader);


        if ($this->config->get('modules.config')) {
            foreach ($this->config->get('modules.config') as $moduleIndex => $moduleName) {
                $loader->registerModule($moduleName);
            }
        }
    }

    protected function syncCoreCache(\KoolKode\Context\Bind\ContainerModuleLoader $loader, \KoolKode\Context\Scope\ScopeLoader $scopes)
    {
        $this->cache->syncContainer($this, $loader, $scopes);
        $this->cache->dumpFile();
    }

    protected function createContainer(\KoolKode\Context\Bind\ContainerModuleLoader $loader, \KoolKode\Context\Scope\ScopeLoader $scopes)
    {
        $contextCachePath = $this->cacheDirectory . DIRECTORY_SEPARATOR . $this->contextName;
        $cacheFile = $contextCachePath . DIRECTORY_SEPARATOR . 'container.php';

        $containerTypeName = __NAMESPACE__ . '\\CompiledContainer';
        $proxyPath = $contextCachePath . DIRECTORY_SEPARATOR . 'scoped';
        if (!is_dir($proxyPath)) {
            if (!mkdir($proxyPath, 0777, true)) {
                throw new ClassNotFoundException('Cannot create dir: ' . $proxyPath);
            }
        }

        $scopedProxies = [];

        foreach ($scopes as $scope) {
            foreach ($scope->getProxyTypeNames() as $typeName) {
                $scopedProxies[] = $typeName;
            }
        }

        if (!is_file($cacheFile) || $this->cache->isModified()) {
            $builder = $this->createContainerBuilder();

            foreach ($loader as $module) {
                $module->build($builder);
            }

            $this->build($builder);

            //$manager = new ReflectionTypeInfoManager();

            $compiler = new \KoolKode\Context\ContainerCompiler('JustImplContainer');
            $code = $compiler->compile($builder, $proxyPath, $scopedProxies);

            file_put_contents($cacheFile, $code);
        }

        require_once $cacheFile;
        $className = 'JustImplContainer';
        return new $className($this->getContainerParams());
    }

    protected function createContainerBuilder()
    {
        $builder = new \KoolKode\Context\Bind\ContainerBuilder();

        foreach ($this->getContainerParams() as $k => $v) {
            $builder->setParameter($k, $v);
        }

        return $builder;
    }

    public function getContainerParams()
    {
        return [
            'core.path' => $this->directory,
            'core.path.cache' => $this->cacheDirectory
        ];
    }

    public function build(\KoolKode\Context\Bind\ContainerBuilder $builder)
    {
    }

    public function invokeBoot(\KoolKode\Context\Bind\ContainerModuleLoader $loader)
    {
        foreach ($loader as $module) {
            $this->invokeBootMethods($module, $this->container);
        }

        $this->invokeBootMethods($this, $this->container);
    }

    public function invokeBootMethods($object, \KoolKode\Context\ContainerInterface $container)
    {
        foreach (get_class_methods($object) as $method) {
            if ('boot' === strtolower(substr($method, 0, 4))) {
                if ($object instanceof static && 'bootkernel' === strtolower($method)) {
                    continue;
                }

                if (4 == strlen($method)) {
                    $object->boot();
                } else {
                    $args = $container->populateArguments(new \ReflectionMethod(get_class($object), $method));

                    switch (count($args)) {
                        case 0:
                            $object->$method();
                            break;
                        case 1:
                            $object->$method($args[0]);
                            break;
                        case 2:
                            $object->$method($args[0], $args[1]);
                            break;
                        case 3:
                            $object->$method($args[0], $args[1], $args[2]);
                            break;
                        case 4:
                            $object->$method($args[0], $args[1], $args[2], $args[3]);
                            break;
                        default:
                            call_user_func_array([$object, $method], $args);
                    }
                }
            }
        }
    }

}