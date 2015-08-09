<?php

RouterRewrite::me()
    ->addRoute(
        'main',
        RouterTransparentRule::create('/')
            ->setDefaults(
                array(
                    'area' => \IndexController::class,
                    'action' => 'index',
                    'module' => false
                )
            )
    );

Application::me()->init(
    $settings = array(
        'environment' => Application::DEFAULT_ENV
    )
);

//if (!__LOCAL_DEBUG__) {
Cache::setPeer(
    SimpleAggregateCache::create()
        ->addPeer('base', WatermarkedPeer::create(RubberFileSystem::create()))
);
//}

Logger::me()
    ->setLevel(LogLevel::finest())
    ->add(SysLogger::create())
    ->add(FileLogger::create(PATH_LOGS));

