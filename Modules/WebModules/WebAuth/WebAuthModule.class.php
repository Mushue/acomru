<?php
/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 08.08.2015
 * Time: 20:22
 */

namespace Modules\WebModules\WebAuth;

use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Scope\Singleton;
use Modules\WebModules\WebAuth\Classes\WebUser;
use Modules\WebModules\WebAuth\Controllers\AuthController;

class WebAuthModule extends AbstractContainerModule
{
    protected $controllers = array(
        AuthController::class
    );

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {
        $builder->bind(\UserAuthInterface::class)
            ->scoped(new Singleton())
            ->to(WebUser::class);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        \RouterRewrite::me()->addRoute(
            'user-login',
            \RouterTransparentRule::create('/login')
                ->setDefaults(
                    array(
                        'area' => AuthController::class,
                        'action' => 'login',
                        'module' => true
                    )
                )
        )
            ->addRoute(
                'user-logout',
                \RouterTransparentRule::create('/logout')
                    ->setDefaults(
                        array(
                            'area' => AuthController::class,
                            'action' => 'logout',
                            'module' => true
                        )
                    )
            );

        $kernel = \Application::get(\WebKernel::class);
        $request = \RouterRewrite::me()->route(\HttpRequest::createFromGlobals());
        $kernel->dropVar(\WebKernel::OBJ_REQUEST)
            ->setRequest($request);

        $area = null;

        $area = \Application::get(\WebKernel::class)
            ->getRequest()
            ->hasAttachedVar('area');

        if ($area) {
            $area = \Application::get(\WebKernel::class)
                ->getRequest()
                ->getAttachedVar('area');
        }
        if (in_array($area, $this->controllers)) {

            /** @var \WebKernel $kernel */
            $pathTemplate = PATH_BASE . 'Modules' . DIRECTORY_SEPARATOR .
                'WebModules' . DIRECTORY_SEPARATOR .
                'WebAuth' . DIRECTORY_SEPARATOR .
                'Views' . DIRECTORY_SEPARATOR;

            \Application::get(\WebKernel::class)
                ->dropVar(\WebKernel::OBJ_PATH_TEMPLATE)
                ->dropVar(\WebKernel::OBJ_PATH_TEMPLATE_DEFAULT)
                ->setPathTemplateDefault($pathTemplate)
                ->setPathTemplate($pathTemplate);
        }
    }

}