<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Configurations' . DIRECTORY_SEPARATOR . 'configuration.inc.php';

if (defined('__LOCAL_DEBUG__') && __LOCAL_DEBUG__) {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

try {

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
                ->setSessionName('acomru')
        )
        ->add(WebAppAjaxHandler::create())
        ->add(WebAppControllerResolverHandler::create())
        ->add(WebAppControllerHandler::create())
        ->add(WebAppViewHandler::create());

    $application->run();

} catch (Exception $e) {

    if (defined('__LOCAL_DEBUG__') && __LOCAL_DEBUG__) {
        $whoops->handleException($e);
    } else {
        Logger::me()->exception($e);
        HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_500));
    }
}