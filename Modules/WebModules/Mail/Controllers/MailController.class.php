<?php
/**
 * Created by PhpStorm.
 * User: pgorbachev
 * Date: 18.08.15
 * Time: 14:10
 */

namespace Modules\WebModules\Mail\Controllers;


class MailController extends \BaseController
{
    protected $methodMap = array(
        'index' => 'indexAction',
    );

    protected $defaultAction = 'index';
    protected $path = 'Views';

    public function indexAction(\HttpRequest $request)
    {
        if ($this->assertAuth()) {
            return $this->getMavRedirectByUrl(\RouterUrlHelper::url(array(), 'user-login'));
        }

        return $this->getMainMav('index', 'Index');
    }

    /**
     * @return \ModelAndView
     */
    protected function assertAuth()
    {
        /** @var \UserAuthInterface $authProvider */
        $authProvider = \Core::get(\UserAuthInterface::class);

        if (!$authProvider->isAuthenticated()) {
            return true;
        }
        return false;
    }

    /**
     * @param string $tpl
     * @param null $path
     * @return \ModelAndView
     */
    protected function getMainMav($tpl = 'index', $path = null)
    {
        $this->model->set('part', new \PartViewer(new \PhpViewResolver(PATH_VIEWS, EXT_TPL), $this->model));
        return parent::getMav($tpl, $path);
    }

    /**
     * @return \ModelAndView
     **/
    public function handleRequest(\HttpRequest $request)
    {
        return $this->resolveAction($request);
    }
}