<?php

final class Application extends Singleton implements IApplication
{
    const DEFAULT_ENV = 'DEVEL';

    /**
     * @var string
     */
    protected $serverType;

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
    }

    protected /* void */
    function environment($environment)
    {
        Assert::isNotEmpty($environment, "'environment' should not be empty");
        $this->serverType = $environment;
    }
}