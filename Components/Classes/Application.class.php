<?php

final class Application extends Singleton implements IApplication
{
    const DEFAULT_ENV = 'DEVEL';
    /**
     * @var \SplStack
     */
    protected static $containerStack;
    /**
     * @var string
     */
    protected $serverType;
    /**
     * @var \KoolKode\Context\Container
     */
    protected $container;

    /** @var  \KoolKode\Config\Configuration */
    protected $config;

    /** @var  \KoolKode\Context\Bind\ContainerBuilder */
    protected $builder;

    protected function __construct()
    {/*_*/
    }

    /**
     * @param $typeName
     * @param InjectionPointInterface|NULL $point
     * @return \KoolKode\Config\Configuration|object
     */
    public static function get($typeName, InjectionPointInterface $point = NULL)
    {
        return static::me()->getContainer()->get($typeName, $point);
    }

    /**
     * @return \KoolKode\Context\Container
     */
    public function getContainer()
    {
        return static::$containerStack->top();
    }

    /**
     * @return Application
     */
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
    }

    /**
     * @param \KoolKode\Context\Bind\BindingInterface $binding
     * @param \KoolKode\Context\InjectionPointInterface|NULL $point
     * @return object
     */
    public static function getBound(\KoolKode\Context\Bind\BindingInterface $binding, \KoolKode\Context\InjectionPointInterface $point = NULL)
    {
        return static::me()->getContainer()->getBound($binding, $point);
    }

    /**
     * @return string
     */
    public function getServerType()
    {
        return $this->serverType;
    }

    /**
     * @param array $settings
     */
    public function init(array $settings = array())
    {
        $this->environment($settings['environment']);

        if (static::$containerStack === NULL) {
            static::$containerStack = new \SplStack();
            $this->builder = new \KoolKode\Context\Bind\ContainerBuilder();
            $this->createContainer();
        }

    }

    /**
     * @param $environment
     * @throws WrongArgumentException
     */
    protected function environment($environment)
    {
        Assert::isNotEmpty($environment, "'environment' should not be empty");
        $this->serverType = $environment;
    }

    /**
     * @return Application
     */
    protected function createContainer()
    {
        $this->builder->bind(WebKernel::class)
            ->scoped(new \KoolKode\Context\Scope\Singleton())
            ->decorate(function (WebKernel $kernel) {
                return WebKernel::create();
            });

        $this->builder->bind(PartViewer::class);

        $this->container = $this->builder->build();


        $this->container->bindInstance(IApplication::class, $this);

        static::$containerStack->push($this->container);

        return $this;
    }

    public function registerModules()
    {
        $loader = new \KoolKode\Config\ConfigurationLoader();
        $loader->registerLoader(new \KoolKode\Config\PhpConfigurationLoader());

        $moduleLoader = new \KoolKode\Context\Bind\ContainerModuleLoader();

        $file = new \SplFileInfo(PATH_MODULES . 'modules.config.php');
        $source = new \KoolKode\Config\ConfigurationSource($file);

        $this->config = $source->loadConfiguration($loader);


        if ($this->config->get('modules.config')) {
            foreach ($this->config->get('modules.config') as $moduleIndex => $moduleName) {
                $moduleLoader->registerModule($moduleName);
            }
        }

        /** @var \KoolKode\Context\Bind\ContainerModuleInterface $module */
        foreach ($moduleLoader as $module) {
            $module->boot();
            $module->build($this->builder);
        }

        $this->container = $this->getContainer();
        $this->container = $this->builder->build();
        static::$containerStack->push($this->container);
    }

    /**
     * @return \KoolKode\Config\Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }
}

