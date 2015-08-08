<?php

namespace Modules\WebModules\Welcome;

use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Scope\Singleton;
use Modules\WebModules\Welcome\Controllers\IndexController;


/**
 * Created by PhpStorm.
 * User: mushu_000
 * Date: 08.08.2015
 * Time: 14:26
 */
class WelcomeModule extends AbstractContainerModule
{

    protected $controllers = array(
        IndexController::class
    );

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        \RouterRewrite::me()->addRoute(
            'welcome-index',
            \RouterTransparentRule::create('/welcome')
                ->setDefaults(
                    array(
                        'area' => IndexController::class,
                        'action' => 'index',
                        'module' => true
                    )
                )
        );

        /** @var \WebKernel $kernel */
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
                'Welcome' . DIRECTORY_SEPARATOR .
                'Views' . DIRECTORY_SEPARATOR;

            \Application::get(\WebKernel::class)
                ->dropVar(\WebKernel::OBJ_PATH_TEMPLATE)
                ->dropVar(\WebKernel::OBJ_PATH_TEMPLATE_DEFAULT)
                ->setPathTemplateDefault($pathTemplate)
                ->setPathTemplate($pathTemplate);
        }
    }

}