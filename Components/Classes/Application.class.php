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

    public function getServerType()
    {
        return $this->serverType;
    }

    public function init(array $settings = array())
    {
        $this->environment($settings['environment']);

        if (static::$containerStack === NULL) {
            static::$containerStack = new \SplStack();
            $this->createContainer();
        }

    }

    protected /* void */
    function environment($environment)
    {
        Assert::isNotEmpty($environment, "'environment' should not be empty");
        $this->serverType = $environment;
    }

    /**
     * @return Application
     */
    protected function createContainer()
    {

        $loader = new ContainerModuleLoader();
        $scopes = new \KoolKode\Context\Scope\ScopeLoader();
        $scopes->registerScope(new \KoolKode\Context\Scope\ApplicationScopeManager());
        $scopes->registerScope(new \KoolKode\Context\Scope\SingletonScopeManager());

        $this->container = new \KoolKode\Context\Container($loader, $scopes);
        $this->container->bindInstance(IApplication::class, $this);
        static::$containerStack->push($this->container);

        foreach ($scopes as $scope) {
            $this->container->registerScope($scope);
        }


        return $this;
    }

    /**
     * @return \KoolKode\Context\Container
     */
    public function getContainer()
    {
        return static::$containerStack->pop();
    }
}