<?php
/**
 * Created by PhpStorm.
 * User: pgorbachev
 * Date: 14.08.15
 * Time: 10:25
 */

namespace Modules\WebModules\Game;


use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use KoolKode\Context\Scope\Singleton;
use Modules\WebModules\Game\Profile\Controllers\ProfileController;
use Modules\WebModules\Game\Profile\UIComponents\GameProfileUIComponent;
use Modules\WebModules\WebAuth\Classes\WebUser;

class GameProfileModule extends AbstractContainerModule
{
    protected $controllers = array(
        ProfileController::class
    );

    public function build(ContainerBuilder $builder)
    {
        $builder->bind(\UserAuthInterface::class)
            ->scoped(new Singleton())
            ->to(WebUser::class);

        $builder->bind(\ProfileUiComponentInterface::class)
            ->scoped(new Singleton())
            ->to(GameProfileUIComponent::class);

    }

    public function boot()
    {
        $this->makeRoute();
        $this->makeTemplatePath();
        $this->makeNavigationLink();
    }

    protected function makeRoute()
    {
        \RouterRewrite::me()
            ->addRoute(
                'profile',
                \RouterTransparentRule::create('/profile')
                    ->setDefaults(
                        array(
                            'area' => ProfileController::class,
                            'action' => 'index',
                            'module' => true
                        )
                    )
            );
    }

    protected function makeTemplatePath()
    {

        $kernel = \Core::get(\WebKernel::class);
        $request = \RouterRewrite::me()->route(\HttpRequest::createFromGlobals());
        $kernel->dropVar(\WebKernel::OBJ_REQUEST)
            ->setRequest($request);

        $area = null;

        $area = $kernel
            ->getRequest()
            ->hasAttachedVar('area');

        if ($area) {
            $area = $kernel
                ->getRequest()
                ->getAttachedVar('area');
        }

        if (in_array($area, $this->controllers)) {
            /** @var \WebKernel $kernel */
            $pathTemplate = PATH_BASE . 'Modules' . DIRECTORY_SEPARATOR .
                'WebModules' . DIRECTORY_SEPARATOR .
                'Game' . DIRECTORY_SEPARATOR .
                'Profile' . DIRECTORY_SEPARATOR .
                'Views' . DIRECTORY_SEPARATOR;

            $kernel
                ->dropVar(\WebKernel::OBJ_PATH_TEMPLATE)
                ->dropVar(\WebKernel::OBJ_PATH_TEMPLATE_DEFAULT)
                ->setPathTemplateDefault($pathTemplate)
                ->setPathTemplate($pathTemplate);
        }
    }

    protected function makeNavigationLink()
    {

        $authProvider = new WebUser();
        /** @var \NavigationBar $navigationBar */
        $navigationBar = \Core::get(\NavigationBar::class);
        if ($authProvider->isAuthenticated()) {
            $navigationBar->unshift(new \NavigationBarElement('Профиль', \RouterUrlHelper::url(array(), 'profile'),
                'profile'),
                new \PositionBarRight());
        }
    }
}