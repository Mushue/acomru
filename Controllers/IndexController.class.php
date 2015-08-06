<?php

/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 05.08.2015
 * Time: 3:25
 */
class WelcomeResource
{
    public function index()
    {
        var_dump(123);
    }
}

class RestResource extends \KoolKode\Context\Bind\Marker
{

    public $pattern;

    public $name;

    public function __construct($pattern, $name)
    {
        $this->pattern = (string)$pattern;
        $this->name = (string)$name;
    }
}

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