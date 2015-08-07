<?php


class IndexController extends BaseController
{
    protected $methodMap = array(
        'index' => 'indexAction',
        'authForbidden' => 'authForbiddenAction',
    );

    protected $defaultAction = 'index';

    public function handleRequest(HttpRequest $request)
    {
        return $this->resolveAction($request);
    }

    public function indexAction(HttpRequest $request)
    {
        $this->meta->setTitle('Внимание! Доступ к ресурсу ограничен.');
        /** @var HttpRequestInterface $httpRequest */
        $httpRequest = Application::me()->getContainer()->get(HttpRequestInterface::class);
        $request = $httpRequest->createFromGlobals();
        return $this->getMav('error');
    }

    public function authForbiddenAction(HttpRequest $request)
    {
        return $this->getMav('auth-forbidden');
    }
}

class TestMarker extends \KoolKode\Context\Bind\Marker
{
    public $name;

    public function __construct($name = '')
    {
        $this->name = trim($name);
    }
}