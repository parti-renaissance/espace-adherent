<?php

namespace AppBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractConsumer implements ConsumerInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(AMQPMessage $message)
    {
        $logger = $this->getLogger();

        try {
            $data = \GuzzleHttp\json_decode($message->body, true);
        } catch (\Exception $e) {
            $logger->error('Message is not valid JSON', [
                'message' => $message->body,
            ]);

            return true;
        }

        $violations = $this->getValidator()->validate($data, new Assert\Collection([
            'allowExtraFields' => false,
            'allowMissingFields' => false,
            'fields' => $this->configureDataConstraints(),
        ]));

        if ($violations->count() > 0) {
            $messages = [];

            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $messages[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            $logger->error('Message structure is not valid', [
                'message' => $message->body,
                'violations' => $messages,
            ]);

            return true;
        }

        return $this->doExecute($data);
    }

    public function writeln($name, $message)
    {
        echo date('Y-m-d H:i:s').' | '.$name.' | '.$message."\n";
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->container->get(LoggerInterface::class);
    }

    private function getValidator(): ValidatorInterface
    {
        return $this->container->get(ValidatorInterface::class);
    }

    /**
     * Creates a list of constraints to validate the message.
     *
     * @return Constraint[]
     */
    abstract protected function configureDataConstraints(): array;

    /**
     * Once the data validated, executes the real message.
     */
    abstract protected function doExecute(array $data): bool;
}
