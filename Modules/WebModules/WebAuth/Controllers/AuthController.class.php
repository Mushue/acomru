<?php


namespace Modules\WebModules\WebAuth\Controllers;


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
        $authProvider = \Application::get(\UserAuthInterface::class);

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
        /** @var \KoolKode\Context\Bind\Binding $binding */
        $binding = \Application::me()->getContainer()->getBinding(\PartViewer::class);
        $binding
            ->resolve('resolver', new \PhpViewResolver(PATH_VIEWS, EXT_TPL))
            ->resolve('model', $this->model);;
        $this->model->set('part', \Application::getBound($binding));
        return parent::getMav($tpl, $path);
    }

    public function logoutAction(\HttpRequest $request)
    {
        /** @var \UserAuthInterface $authProvider */
        $authProvider = \Application::get(\UserAuthInterface::class);

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