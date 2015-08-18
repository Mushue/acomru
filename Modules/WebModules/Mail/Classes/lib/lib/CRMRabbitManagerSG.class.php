<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CRMRabbitManagerSG
 *
 * @author p.gorbachev
 */
class CRMRabbitManagerSG implements ICRMRabbitManager
{

    /*
        AMQPConnection - реализация соединения с брокером
        AMQPChannel - работа с каналами передачи данных
        AMQPExchange - отправка сообщений
        AMQPQueue - очередь сообщений
        AMQPEnvelope - сообщения
    */

    /* @var $connection \AMQPConnection */
    protected $connection = null;
    /* @var $channel \AMQPChannel */
    protected $channel = null;
    /* @var $queue \AMQPQueue */
    protected $queue = null;
    /* @var $exchange \AMQPExchange */
    protected $exchange = null;
    /* @var $envelope \AMQPEnvelope */
    protected $envelope = null;

    protected $connected = false;

    protected $queueName = null;
    protected $exchangeName = null;
    protected $routingKey = null;


    /** Setters and Getters **/

    /**
     *
     * @return string
     */
    function getQueueName()
    {
        return $this->queueName;
    }

    /**
     *
     * @param string $channelName
     * @return \CRMRabbitManagerSG
     */
    function setQueueName($queueName)
    {
        $this->queueName = $queueName;
        return $this;
    }

    /**
     *
     * @return string
     */
    function getExchangeName()
    {
        return $this->exchangeName;
    }

    /**
     *
     * @param string $exchangeName
     * @return \CRMRabbitManagerSG
     */
    function setExchangeName($exchangeName)
    {
        $this->exchangeName = $exchangeName;
        return $this;
    }

    /**
     *
     * @return string
     */
    function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     *
     * @param string $routingKey
     * @return \CRMRabbitManagerSG
     */
    function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    function isConnected()
    {
        return $this->connected;
    }

    /**
     *
     * @param boolean $connected
     * @return \CRMRabbitManager
     */
    function setConnected($connected)
    {
        $this->connected = (bool)$connected;
        return $this;
    }

    /**
     *
     * @return \AMQPConnection
     */
    function getConnection()
    {
        return $this->connection;
    }

    /**
     *
     * @param \AMQPConnection $connection
     * @return \CRMRabbitManager
     */
    function setConnection(\AMQPConnection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     *
     * @return \AMQPChannel
     */
    function getChannel()
    {
        return $this->channel;
    }

    /**
     *
     * @param \AMQPChannel $channel
     * @return \CRMRabbitManager
     */
    function setChannel(\AMQPChannel $channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     *
     * @return \AMQPQueue
     */
    function getQueue()
    {
        return $this->queue;
    }

    /**
     *
     * @param \AMQPQueue $queue
     * @return \CRMRabbitManager
     */
    function setQueue(\AMQPQueue $queue)
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     *
     * @return \AMQPExchange
     */
    function getExchange()
    {
        return $this->exchange;
    }

    /**
     *
     * @param \AMQPExchange $exchange
     * @return \CRMRabbitManager
     */
    function setExchange($exchange)
    {
        $this->exchange = $exchange;
        return $this;
    }

    /**
     *
     * @return \AMQPEnvelope
     */
    function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     *
     * @param \AMQPEnvelope $envelope
     * @return \CRMRabbitManagerSG
     */
    function setEnvelope(\AMQPEnvelope $envelope)
    {
        $this->envelope = $envelope;
        return $this;
    }

}
