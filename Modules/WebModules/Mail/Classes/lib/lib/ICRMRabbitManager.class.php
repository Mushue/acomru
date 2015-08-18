<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author p.gorbachev
 */
interface ICRMRabbitManager
{

    /**
     *
     * @return string
     */
    function getQueueName();

    /**
     *
     * @return string
     */
    function getExchangeName();

    /**
     *
     * @return string
     */
    function getRoutingKey();

    /**
     *
     * @param string $channelName
     * @return \CRMRabbitManagerSG
     */
    function setQueueName($queueName);

    /**
     *
     * @param string $exchangeName
     * @return \CRMRabbitManagerSG
     */
    function setExchangeName($exchangeName);

    /**
     *
     * @param string $routingKey
     * @return \CRMRabbitManagerSG
     */
    function setRoutingKey($routingKey);

    /**
     *
     * @return boolean
     */
    function isConnected();

    /**
     *
     * @param boolean $connected
     * @return \CRMRabbitManager
     */
    function setConnected($connected);

    /**
     *
     * @return \AMQPConnection
     */
    function getConnection();

    /**
     *
     * @return \AMQPChannel
     */
    function getChannel();

    /**
     *
     * @return \AMQPQueue
     */
    function getQueue();

    /**
     *
     * @param \AMQPConnection $connection
     * @return \CRMRabbitManager
     */
    function setConnection(\AMQPConnection $connection);

    /**
     *
     * @param \AMQPChannel $channel
     * @return \CRMRabbitManager
     */
    function setChannel(\AMQPChannel $channel);

    /**
     *
     * @param \AMQPQueue $queue
     * @return \CRMRabbitManager
     */
    function setQueue(\AMQPQueue $queue);

    /**
     *
     * @return \AMQPExchange
     */
    function getExchange();

    /**
     *
     * @param \AMQPExchange $exchange
     * @return \CRMRabbitManager
     */
    function setExchange($exchange);

    /**
     *
     * @return \AMQPEnvelope
     */
    function getEnvelope();

    /**
     *
     * @param \AMQPEnvelope $envelope
     * @return \CRMRabbitManagerSG
     */
    function setEnvelope(\AMQPEnvelope $envelope);
}