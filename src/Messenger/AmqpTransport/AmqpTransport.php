<?php

namespace App\Messenger\AmqpTransport;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport as SymfonyAmqpTransport;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\Connection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @see https://github.com/symfony/symfony/issues/42825#issuecomment-984376925
 */
class AmqpTransport extends SymfonyAmqpTransport
{
    protected Connection $connection;

    public function __construct(Connection $connection, ?SerializerInterface $serializer = null)
    {
        $this->connection = $connection;

        parent::__construct($connection, $serializer);
    }

    public function send(Envelope $envelope): Envelope
    {
        try {
            return parent::send($envelope);
        } catch (TransportException|\AMQPConnectionException $e) {
            /** @var RetryAfterAMQPExceptionStamp|null $stamp */
            $stamp = $envelope->last(RetryAfterAMQPExceptionStamp::class);

            if (null === $stamp) {
                $stamp = new RetryAfterAMQPExceptionStamp();
                $envelope = $envelope->with($stamp);
            } else {
                $stamp->increaseRetryAttempts();
            }

            if ($stamp->getRetryAttempts() < 3) {
                /*
                 * We only disconnect: the Connection object will be cleaned and AmqpConnection reconnected afterwards
                 * @see \Symfony\Component\Messenger\Bridge\Amqp\Transport\Connection::clearWhenDisconnected()
                 */
                $this->connection->channel()->getConnection()->disconnect();

                return $this->send($envelope);
            }

            throw $e;
        }
    }
}
