<?php

namespace Tests\App\Test\OldSoundRabbitMq\Connection;

class DummyChannel
{
    public array $logs = [];
    public array $exchangesQueues = [];
    public array $queues = [];
    public array $messages = [];

    public function basic_publish($msg, $exchange = '', $routingKey = '')
    {
        $queue = $this->findQueueForRoutingKey($exchange, $routingKey);
        $this->messages[$exchange][$queue][] = $msg;
    }

    public function queue_declare($queue = '')
    {
        if (!isset($this->queues[$queue])) {
            $this->queues[$queue] = [];
        }

        return [$queue];
    }

    public function exchange_declare($exchange)
    {
        if (!isset($this->messages[$exchange])) {
            $this->messages[$exchange] = [];
        }
    }

    public function queue_bind($queue, $exchange, $routingKey = '')
    {
        if (!\in_array($queue, $this->exchangesQueues[$exchange] ?? [])) {
            $this->exchangesQueues[$exchange][] = $queue;
        }

        $this->queues[$queue][] = $routingKey;
    }

    public function queue_purge($queue = '')
    {
        foreach ($this->findExchangesForQueue($queue) as $exchange) {
            $this->messages[$exchange][$queue] = [];
        }
    }

    public function getChannelId(): int
    {
        return 1;
    }

    public function basic_get($queue = '')
    {
        $exchange = current($this->findExchangesForQueue($queue));

        $message = array_shift($this->messages[$exchange][$queue]);

        return $message ?: null;
    }

    public function countMessage(string $queue): int
    {
        $exchange = current($this->findExchangesForQueue($queue));

        return \count($this->messages[$exchange][$queue]);
    }

    public function __call(string $methodName, $args)
    {
        $this->logs[$methodName][] = $args;
    }

    private function findQueueForRoutingKey(string $exchange, string $routingKey): ?string
    {
        foreach ($this->exchangesQueues[$exchange] as $queue) {
            foreach ($this->queues[$queue] as $bindRoutingKey) {
                if (
                    ('*' === substr($bindRoutingKey, -1) && 0 === strpos($routingKey, rtrim($bindRoutingKey, '*')))
                    || $bindRoutingKey === $routingKey
                ) {
                    return $queue;
                }
            }
        }

        return null;
    }

    private function findExchangesForQueue(string $queue): array
    {
        $exchanges = [];

        foreach ($this->exchangesQueues as $exchange => $queues) {
            if (\in_array($queue, $queues)) {
                $exchanges[] = $exchange;
            }
        }

        return $exchanges;
    }
}
