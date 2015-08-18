<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CRMRabbitManager
 *
 * @author p.gorbachev
 */
class CRMRabbitManager extends CRMRabbitManagerSG
{

    public function __construct(\AMQPConnection $connection)
    {
        $this->connection = $connection;
        if (!$this->isConnected()) {
            $this->connection->connect();
            $this->setConnected(true);
        }
    }

    public function setNamesEQR($queue_name = 'mmp_events', $exchange_name = 'mmp', $routing_key = 'event.#')
    {
        $this->setExchangeName($exchange_name);
        $this->setQueueName($queue_name);
        $this->setRoutingKey($routing_key);
        if ($this->getQueue()) {
            $this->getQueue()->setName($queue_name);
            $this->getQueue()->bind($exchange_name, $routing_key);
        }
    }

    public function sendAck()
    {
        if ($this->getQueue()) {
            /** Поддтверждаем получение сообщения передав тэг доставки сообщения*/
            $this->getQueue()->ack($this->getEnvelope()->getDeliveryTag());
        }
    }

    public function sendNAck()
    {
        if ($this->getQueue()) {
            $this->getQueue()->nack($this->getEnvelope()->getDeliveryTag(), AMQP_REQUEUE);
        }
    }

    public function envelopeProcess($flags = AMQP_NOPARAM)
    {
        if ($this->getQueue()) {
            try {
                $envelope = $this->getQueue()->get($flags);
                if ($envelope instanceof \AMQPEnvelope) {
                    $this->setEnvelope($envelope);
                    return true;
                }
            } catch (Exception $e) {
                echo $e->getTraceAsString();
            }
        }
        return false;
    }

}
