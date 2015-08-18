<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IEventProcessorPlugin
 *
 * @author p.gorbachev
 */
interface IEventProcessorPlugin
{

    public function setBody($body);

    public function getBody();

    public function setRoutingKey($key);

    public function getRoutingKey();

    public function setAppId($appId);

    public function getAppId();

    public function setMessageId($messageId);

    public function getMessageId();

    public function setCorrelationId($correlationId);

    public function getCorrelationId();

    public function process(\ICRMRabbitManager $manager);

}
