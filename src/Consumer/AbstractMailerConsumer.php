<?php

namespace AppBundle\Consumer;

use AppBundle\Mailer\EmailClientInterface;
use AppBundle\Mailer\Model\EmailRepository;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractMailerConsumer extends AbstractConsumer
{
    protected function configureDataConstraints(): array
    {
        return [
            'uuid' => [new Assert\NotBlank()],
        ];
    }

    protected function doExecute(array $data): bool
    {
        $logger = $this->getLogger();

        try {
            if (!$message = $this->getEmailRepository()->findOneByUuid($data['uuid'])) {
                $logger->error('Email not found', $data);
                $this->writeln($data['uuid'], 'Email not found, rejecting');

                return true;
            }

            $this->writeln($data['uuid'], 'Delivering '.$message->getEnglishLog());
            $delivered = $this->getClient()->sendEmail($message->getRequestPayloadJson());

            if (!$delivered) {
                $this->writeln($data['uuid'], 'An issue occured, requeuing');
            } else {
                $this->getEmailRepository()->setDelivered($message, $delivered);
            }

            return $delivered;
        } catch (ConnectException $error) {
            $this->writeln($data['uuid'], 'API timeout');
            $logger->error('RabbitMQ API timeout while sending a mail with UUID '.$data['uuid'], ['exception' => $error]);

            // to prevent requeuing in loop and user from receiving tens of mails
            return true;
        } catch (\Exception $error) {
            $logger->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }

    protected function getClient(): EmailClientInterface
    {
        return $this->container->get($this->getClientId());
    }

    protected function getEmailRepository(): EmailRepository
    {
        return $this->getDoctrine()->getRepository($this->getEmailEntityClass());
    }

    abstract protected function getClientId(): string;

    abstract protected function getEmailEntityClass(): string;
}
