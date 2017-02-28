<?php

namespace AppBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;

abstract class AbstractConsumer implements ConsumerInterface
{
    use ContainerAwareTrait;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * Create a list of contraints to validate the message.
     *
     * @return Constraint[]
     */
    abstract protected function configureDataConstraints(): array;

    /**
     * Once the data validated, execute the real message.
     *
     * @param array $data the data of the message
     *
     * @return bool
     */
    abstract public function doExecute(array $data): bool;

    public function execute(AMQPMessage $message)
    {
        $logger = $this->container->get('logger');
        $validator = $this->container->get('validator');

        try {
            $data = \GuzzleHttp\json_decode($message->body, true);
        } catch (\Exception $e) {
            $logger->error('Message is not valid JSON', [
                'message' => $message->body,
            ]);

            return true;
        }

        $violations = $validator->validate($data, new Assert\Collection([
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
}
