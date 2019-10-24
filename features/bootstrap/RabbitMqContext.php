<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Coduo\PHPMatcher\PHPMatcher;
use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RabbitMqContext implements Context
{
    const QUEUES = [
        'api_sync',
        'mailer-delayed-campaign',
        'mailer-delayed-transactional',
        'referent-message-dispatcher',
        'deputy-message-dispatcher',
    ];

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @BeforeScenario
     *
     * @Given I clean the queues
     */
    public function cleanQueues()
    {
        foreach (self::QUEUES as $queue) {
            $this->iCleanTheQueue($queue);
        }
    }

    /**
     * @Given I clean the :queue queue
     */
    public function iCleanTheQueue($queue)
    {
        $this->container->get('old_sound_rabbit_mq.connection.default')->channel()->queue_purge($queue);
    }

    /**
     * @Then print :queue messages
     */
    public function printProducerMessages(string $queue): void
    {
        $messages = $this->getMessages($queue);

        if (!\count($messages)) {
            echo 'No message';

            return;
        }

        /** @var AMQPMessage $message */
        foreach ($messages as $message) {
            echo sprintf("exchange: %s\n", $this->getMessageValue($message, 'exchange'));
            echo sprintf("routing key: %s\n", $this->getMessageValue($message, 'routing_key'));
            echo sprintf("body: %s\n", $this->getMessageValue($message, 'body'));
            echo '----';
        }
    }

    /**
     * @When :producer publishes :body
     * @When :producer publishes :body with :routingKey routing key
     */
    public function producerPublishes(string $producer, string $body, string $routingKey = ''): void
    {
        $this->getProducer($producer)->publish($body, $routingKey);
    }

    /**
     * @Then :queue should have :number message(s)
     */
    public function producerShouldHavePublishedMessages(string $queue, int $number): void
    {
        $messagesCount = $this->countMessage($queue);

        if ($messagesCount !== $number) {
            $plural = $number < 2 ? 'message' : 'messages';
            $errorMessage = sprintf('%d %s published, but should be %d', $messagesCount, $plural, $number);

            throw new \LogicException($errorMessage);
        }
    }

    /**
     * @Then :queue should have message(s) below:
     */
    public function producerShouldHavePublishedMessagesBelow(string $queue, TableNode $tableNode): void
    {
        $messages = $this->getMessages($queue);

        foreach ($tableNode->getColumnsHash() as $expectedHash) {
            $found = array_reduce(
                $messages,
                function (bool $carry, AMQPMessage $message) use ($expectedHash): bool {
                    return $carry || $this->assertMessage($message, $expectedHash);
                },
                false
            );

            if (!$found) {
                $messagesString = implode("\n", array_map(function ($msg) { return $msg->getBody(); }, $messages));
                throw new \LogicException("Expected message not found among these ones:\n$messagesString");
            }
        }
    }

    /**
     * @When consumer :consumer consumes :count message(s)
     */
    public function consumerConsumesMessages(string $consumer, int $count): void
    {
        $this->getConsumer($consumer)->consume($count);
    }

    private function getProducer(string $producer): Producer
    {
        $service = sprintf('old_sound_rabbit_mq.%s_producer', $producer);

        return $this->container->get($service);
    }

    private function getConsumer(string $consumer): Consumer
    {
        $service = sprintf('old_sound_rabbit_mq.%s_consumer', $consumer);

        return $this->container->get($service);
    }

    public function countMessage(string $producer): int
    {
        $channel = $this->container->get('old_sound_rabbit_mq.connection.default')->channel();

        // We use the same name for queue and producer.
        list(, $count) = $channel->queue_declare($producer, false, true, false, false);

        return $count;
    }

    public function getMessages(string $queue): array
    {
        $channel = $this->container->get('old_sound_rabbit_mq.connection.default')->channel();
        $messages = [];

        /** @var AMQPMessage $message */
        while ($message = $channel->basic_get($queue)) {
            $messages[] = $message;
            $channel->basic_ack($message->get('delivery_tag'));
        }

        return $messages;
    }

    private function assertMessage(AMQPMessage $message, array $hash): bool
    {
        foreach ($hash as $key => $expected) {
            if (!PHPMatcher::match($this->getMessageValue($message, $key), $expected, $error)) {
                return false;
            }
        }

        return true;
    }

    private function getMessageValue(AMQPMessage $message, string $key): ?string
    {
        switch ($key) {
            case 'exchange':
                return $message->delivery_info['exchange'];
            case 'routing_key':
                return $message->delivery_info['routing_key'];
            case 'body':
                return $message->getBody();
            default:
                return $message->get($key);
        }
    }
}
