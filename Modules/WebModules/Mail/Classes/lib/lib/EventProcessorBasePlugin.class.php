<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EventProcessorBasePlugin
 *
 * @author p.gorbachev
 */
class EventProcessorBasePlugin implements IEventProcessorPlugin
{

    protected $appId = null;
    protected $body = null;
    protected $correlationId = null;
    protected $messageId = null;
    protected $routingKey = null;

    public function process(\ICRMRabbitManager $manager)
    {
        throw new Exception('Not implemented');
    }

    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    function setMessageId($messageId)
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    function setCorrelationId($correlationId)
    {
        $this->correlationId = $correlationId;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }


}
