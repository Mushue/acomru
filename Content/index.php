<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Configurations' . DIRECTORY_SEPARATOR . 'Global' . DIRECTORY_SEPARATOR . 'configuration.inc.php';

if (defined('__LOCAL_DEBUG__') && __LOCAL_DEBUG__) {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

try {
    $core = new Core();
    $core->run();

} catch (Exception $e) {

    if (defined('__LOCAL_DEBUG__') && __LOCAL_DEBUG__) {
        $whoops->handleException($e);
    } else {
        Logger::me()->exception($e);
        HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_500));
    }
}