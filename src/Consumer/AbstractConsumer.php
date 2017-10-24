<?php

namespace AppBundle\Consumer;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractConsumer implements ConsumerInterface
{
    use LoggerAwareTrait;

    protected $logger;
    protected $validator;
    protected $registry;

    public function __construct(ValidatorInterface $validator, Registry $registry)
    {
        $this->validator = $validator;
        $this->registry = $registry;
    }

    public function execute(AMQPMessage $message)
    {
        try {
            $data = \GuzzleHttp\json_decode($message->body, true);
        } catch (\Exception $e) {
            $this->getLogger()->error('Message is not valid JSON', [
                'message' => $message->body,
            ]);

            return ConsumerInterface::MSG_ACK;
        }

        if ($messages = $this->validate($data)) {
            $this->getLogger()->error('Message structure is not valid', [
                'message' => $message->body,
                'violations' => $messages,
            ]);

            return ConsumerInterface::MSG_ACK;
        }

        return $this->doExecute($data);
    }

    public function writeln($name, $message): void
    {
        echo $name.' | '.$message.PHP_EOL;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    private function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    protected function getDoctrine(): Registry
    {
        return $this->registry;
    }

    protected function getManager(): ObjectManager
    {
        return $this->getDoctrine()->getManager();
    }

    private function validate(array $data): ? array
    {
        $violations = $this->getValidator()->validate($data, new Assert\Collection([
            'allowExtraFields' => false,
            'allowMissingFields' => false,
            'fields' => $this->configureDataConstraints(),
        ]));

        if (!$violations->count()) {
            return null;
        }

        $messages = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $messages[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $messages;
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
    abstract protected function doExecute(array $data): int;
}
