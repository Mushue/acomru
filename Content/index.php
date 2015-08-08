<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Configurations' . DIRECTORY_SEPARATOR . 'configuration.inc.php';

if (defined('__LOCAL_DEBUG__') && __LOCAL_DEBUG__) {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

try {

    $request = RouterRewrite::me()->route(HttpRequest::createFromGlobals());
    Session::start();

    $webKernel = Application::me()->getContainer()->get(WebKernel::class)
        ->dropVar(WebKernel::OBJ_REQUEST)
        ->setRequest($request)
        ->setPathWeb(PATH_WEB)
        ->setPathController(PATH_CONTROLLERS)
        ->setPathTemplate(PATH_VIEWS)
        ->setPathTemplateDefault(PATH_VIEWS)
        ->setServiceLocator(Application::get(ServiceLocator::class))
        ->add(WebKernelBufferHandler::create())
        ->add(WebKernelSessionHandler::create()
            ->setCookieDomain(COOKIE_HOST_NAME)
            ->setSessionName('acomru')
        );

    Application::me()->registerModules();

    $webKernel->add(WebKernelAjaxHandler::create())
        ->add(WebKernelControllerResolverHandler::create())
        ->add(WebKernelControllerHandler::create())
        ->add(WebKernelViewHandler::create());

    $webKernel->run();

} catch (Exception $e) {

    if (defined('__LOCAL_DEBUG__') && __LOCAL_DEBUG__) {
        $whoops->handleException($e);
    } else {
        Logger::me()->exception($e);
        HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_500));
    }
}