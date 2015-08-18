<?php
/**
 * Created by PhpStorm.
 * User: pgorbachev
 * Date: 18.08.15
 * Time: 14:05
 */

namespace Modules\WebModules\Mail;


use KoolKode\Context\Bind\AbstractContainerModule;
use KoolKode\Context\Bind\ContainerBuilder;
use Modules\WebModules\Mail\Controllers\MailController;
use Modules\WebModules\WebAuth\Classes\WebUser;

class MailModule extends AbstractContainerModule
{
    protected $controllers = array(
        MailController::class
    );

    public function build(ContainerBuilder $builder)
    {

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
                'mail',
                \RouterTransparentRule::create('/mail')
                    ->setDefaults(
                        array(
                            'area' => MailController::class,
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
                'Mail' . DIRECTORY_SEPARATOR .
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
        $postHtml = '';
        $authProvider = new WebUser();
        /** @var \NavigationBar $navigationBar */
        $navigationBar = \Core::get(\NavigationBar::class);
        $postHtml = ' <span class="badge">%d</span>';
        $postHtml = sprintf($postHtml, 12);

        if ($authProvider->isAuthenticated()) {

            $navigationBar->unshift(new \NavigationBarElement('Почта' . $postHtml, \RouterUrlHelper::url(array(),
                'mail'), 'mail'),
                new \PositionBarRight());
        }
    }
}