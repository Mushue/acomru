<?php
/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 08.08.2015
 * Time: 14:30
 */

namespace Modules\WebModules\Welcome\Controllers;


use HttpRequest;
use ModelAndView;

class IndexController extends \BaseController
{
    protected $methodMap = array(
        'index' => 'indexAction',
        'authForbidden' => 'authForbiddenAction',
    );

    protected $defaultAction = 'index';
    protected $path = 'Views';

    /**
     * @return ModelAndView
     **/
    public function handleRequest(HttpRequest $request)
    {
        return $this->resolveAction($request);
    }

    public function indexAction(HttpRequest $request)
    {
        $this->meta->setTitle('Приветттттттт!!!!!!!!!!!!!!');
        return $this->getMav('error', 'Index');
    }

    public function authForbiddenAction(HttpRequest $request)
    {
        return $this->getMav('auth-forbidden');
    }

}