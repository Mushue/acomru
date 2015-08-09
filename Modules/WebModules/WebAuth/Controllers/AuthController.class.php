<?php


namespace Modules\WebModules\WebAuth\Controllers;


use KoolKode\Context\Container;

class AuthController extends \BaseController
{
    protected $methodMap = array(
        'login' => 'loginAction',
        'logout' => 'logoutAction',
        'authForbidden' => 'authForbiddenAction',
    );

    protected $defaultAction = 'login';
    protected $path = 'Views';

    /**
     * @return \ModelAndView
     **/
    public function handleRequest(\HttpRequest $request)
    {
        return $this->resolveAction($request);
    }

    public function loginAction(\HttpRequest $request)
    {
        /** @var \UserAuthInterface $authProvider */
        $authProvider = \Core::get(\UserAuthInterface::class);

        if ($request->getPost()) {
            $user = new \User();
            $user->setId(uniqid());
            $authProvider->authenticate($user);
        }

        if ($authProvider->isAuthenticated()) {
            return $this->getMavRedirectByUrl('/');
        }

        return $this->getMainMav('login', 'Index');
    }

    protected function getMainMav($tpl = 'index', $path = null)
    {
        $this->model->set('part', new \PartViewer(new \PhpViewResolver(PATH_VIEWS, EXT_TPL), $this->model));
        return parent::getMav($tpl, $path);
    }

    public function logoutAction(\HttpRequest $request)
    {
        /** @var \UserAuthInterface $authProvider */
        $authProvider = \Core::get(\UserAuthInterface::class);

        if ($authProvider->isAuthenticated()) {
            $authProvider->logout();
        }
        return $this->getMavRedirectByUrl('/');
    }

    public function authForbiddenAction(\HttpRequest $request)
    {
        return $this->getMav('auth-forbidden');
    }

}