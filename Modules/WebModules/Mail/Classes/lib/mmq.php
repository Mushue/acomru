<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require __DIR__ . '/lib/ICRMRabbitManager.class.php';
require __DIR__ . '/lib/CRMRabbitManagerSG.class.php';
require __DIR__ . '/lib/CRMRabbitManager.class.php';
require __DIR__ . '/lib/CRMMessageManager.class.php';
require __DIR__ . '/lib/IEventProcessorPlugin.class.php';
require __DIR__ . '/lib/EventProcessorBasePlugin.class.php';


$rabbit = new \AMQPConnection(array(
        'host' => '192.168.100.24',
        'port' => '5672',
        'login' => 'admin',
        'password' => 'qwertY11'
    )
);

$manager = new \CRMRabbitManager($rabbit);
$crm = new \CRMMessageManager();

$manager->setChannel(new \AMQPChannel($manager->getConnection()))
    ->setQueue(new \AMQPQueue($manager->getChannel()))
    ->setNamesEQR();
if ($manager->envelopeProcess()) {
    $crm->processMessage($manager);
}

$manager->getconnection()->disconnect();

