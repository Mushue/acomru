<?php

try {

    require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Configurations' . DIRECTORY_SEPARATOR . 'configuration.inc.php';

    if (defined('DEBUG') && DEBUG) {
        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }

    $application = WebApplication::create()
        //->setRequest($request)
        ->setPathWeb(PATH_WEB)
        ->setPathController(PATH_CONTROLLERS)
        ->setPathTemplate(PATH_VIEWS)
        ->setPathTemplateDefault(PATH_VIEWS)
        ->setServiceLocator(ServiceLocator::create())
        ->add(WebAppBufferHandler::create())
        ->add(
            WebAppSessionHandler::create()
                ->setCookieDomain(COOKIE_HOST_NAME)
                ->setSessionName('a1crm')
        )
        ->add(WebAppAjaxHandler::create())
        ->add(WebAppLinkerInjector::create()->setLogClassName('SiteLog')->setBaseUrl(PATH_WEB_URL))
        ->add(WebAppControllerResolverHandler::create())
        ->add(WebAppControllerHandler::create())
        ->add(WebAppViewHandler::create());

    $application->run();

} catch (Exception $e) {
    HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_500));
}