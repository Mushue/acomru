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

    protected function __construct()
    {/*_*/
    }

    /**
     * @return Application
     */
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
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

        $builder = new \KoolKode\Context\Bind\ContainerBuilder();

        /** @var \KoolKode\Context\Bind\ContainerModuleInterface $module */
        foreach ($moduleLoader as $module) {
            $module->build($builder);
        }

        $this->container = $builder->build();
        $this->container->bindInstance(IApplication::class, $this);

        static::$containerStack->push($this->container);

        return $this;
    }

    /**
     * @return \KoolKode\Context\Container
     */
    public function getContainer()
    {
        return static::$containerStack->top();
    }

    /**
     * @return \KoolKode\Config\Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }
}

