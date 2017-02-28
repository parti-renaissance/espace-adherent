<?php

namespace AppBundle\Consumer\Mailjet;

use AppBundle\Consumer\AbstractConsumer;
use AppBundle\Mailjet\Message\MailjetMessage;
use AppBundle\Mailjet\Message\MailjetMessageRecipient;
use AppBundle\Mailjet\Message\ReferentMessage;
use GuzzleHttp\Exception\ConnectException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class ReferentMessageMailjetConsumer extends AbstractConsumer
{
    const NAME = 'mailjet-referent';

    protected function configureDataConstraints(): array
    {
        return [
            'uuid' => [new Assert\NotBlank()],
            'message' => [new Assert\NotBlank()],
        ];
    }

    public function doExecute(array $data): bool
    {
        $logger = $this->container->get('logger');
        $mailjet = $this->container->get('app.mailjet.client.campaign');

        try {
            $this->writeln(self::NAME, 'Sending email '.$data['uuid']);

            $message = @unserialize($data['message'], [
                'allowed_classes' => [
                    ReferentMessage::class,
                    MailjetMessage::class,
                    MailjetMessageRecipient::class,
                    Uuid::class,
                ],
            ]);

            if (!$message) {
                $logger->error('Message not unserializable', $data);
                $this->writeln(self::NAME, 'Message not unserializable');

                return true;
            }

            $delivered = $mailjet->sendMessage($message);

            if (!$delivered) {
                $this->writeln(self::NAME, 'An issue occured, requeuing');
            }

            return $delivered;
        } catch (ConnectException $error) {
            $this->writeln(self::NAME, 'API timeout, requeuing');

            return false;
        } catch (\Exception $error) {
            $logger->error('Consumer mailjet-referent failed', ['exception' => $error]);

            throw $error;
        }
    }
}
