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

AMQPPool::me()->addLink(
    'local', new AMQPPecl(
        AMQPCredentials::create()
            ->setHost('127.0.0.1')
            ->setPort('5432')
            ->setLogin('guest')
            ->setPassword('guest')
    )
);

Logger::me()
    ->setLevel(LogLevel::finest())
    ->add(SysLogger::create())
    ->add(FileLogger::create(PATH_LOGS));

