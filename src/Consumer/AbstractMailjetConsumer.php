<?php

namespace AppBundle\Consumer;

use AppBundle\Mailjet\MailjetClient;
use AppBundle\Repository\MailjetEmailRepository;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Validator\Constraints as Assert;

class AbstractMailjetConsumer extends AbstractConsumer
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
            $this->writeln(static::NAME, 'Sending email '.$data['uuid']);

            if (!$message = $this->getMailjetRepository()->findOneByUuid($data['uuid'])) {
                $logger->error('Message not not found for uuid "%s"', $data['uuid']);
                $this->writeln(static::NAME, 'Message not found');

                return true;
            }

            $delivered = $this->getMailjetClient()->sendEmail($message->getRequestPayloadJson());

            if (!$delivered) {
                $this->writeln(static::NAME, 'An issue occured, requeuing');
            } else {
                $this->getMailjetRepository()->setDelivered($message, $delivered);
            }

            return $delivered;
        } catch (ConnectException $error) {
            $this->writeln(static::NAME, 'API timeout, requeuing');

            return false;
        } catch (\Exception $error) {
            $logger->error('Consumer '.static::NAME.' failed', ['exception' => $error]);

            throw $error;
        }
    }

    protected function getMailjetClient(): MailjetClient
    {
        return $this->container->get(static::CLIENT_ID);
    }

    protected function getMailjetRepository(): MailjetEmailRepository
    {
        return $this->container->get('app.repository.mailjet_email');
    }
}
