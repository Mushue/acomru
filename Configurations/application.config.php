<?php


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
