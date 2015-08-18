<?php
/**
 * Created by PhpStorm.
 * User: pgorbachev
 * Date: 18.08.15
 * Time: 15:34
 */

namespace Modules\WebModules\Mail\Classes;


class AMQPMailMessage
{
    /** @var  \AMQPConnection */
    protected $messageServer;
    /** @var  \AMQPChannel */
    protected $channel;
    /** @var  \AMQPQueue */
    protected $queue;

    /**
     * AMQPMailMessage constructor.
     */
    public function __construct()
    {
        /*
        $this->messageServer = new \AMQPConnection();
        $this->messageServer->connect();

        $this->channel = new \AMQPChannel($this->messageServer);
        $this->queue = new \AMQPQueue($this->channel);

        $this->queue->declareQueue();

        $this->queue->bind('user.mail','user.1.#');
        */
    }

    /**
     * @return \AMQPEnvelope|bool
     */
    public function get()
    {
        return false;// $this->queue->get();
    }
}