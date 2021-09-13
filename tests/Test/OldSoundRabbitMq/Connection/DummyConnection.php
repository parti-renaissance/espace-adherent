<?php

namespace Tests\App\Test\OldSoundRabbitMq\Connection;

use PhpAmqpLib\Connection\AbstractConnection;

class DummyConnection extends AbstractConnection
{
    private ?DummyChannel $channel = null;
    public AbstractConnection $decorated;

    public function __construct(AbstractConnection $decorated)
    {
        $this->decorated = $decorated;
    }

    public function channel($channelId = null)
    {
        if ($this->channel) {
            return $this->channel;
        }

        return $this->channel = new DummyChannel();
    }
}
