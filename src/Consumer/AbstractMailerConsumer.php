<?php

namespace App\Consumer;

use App\Exception\InvalidUuidException;
use App\Mailer\EmailClientInterface;
use App\Mailer\Exception\MailerException;
use App\Repository\EmailRepository;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractMailerConsumer extends AbstractConsumer
{
    protected $emailClient;
    protected $emailRepository;

    protected function configureDataConstraints(): array
    {
        return [
            'uuid' => [new Assert\NotBlank(), new Assert\Uuid()],
        ];
    }

    protected function doExecute(array $data): int
    {
        try {
            if (!$message = $this->getEmailRepository()->findOneByUuid($data['uuid'])) {
                $this->getLogger()->error('Email not found', $data);
                $this->writeln($data['uuid'], 'Email not found, rejecting');

                return ConsumerInterface::MSG_ACK;
            }

            $this->writeln($data['uuid'], 'Delivering '.$message->getEnglishLog());
            $delivered = $this->getEmailClient()->sendEmail($message->getRequestPayloadJson());

            if (!$delivered) {
                $this->writeln($data['uuid'], 'An issue occured, requeuing');
            } else {
                $this->getEmailRepository()->setDelivered($message, $delivered);
            }

            return $delivered ? ConsumerInterface::MSG_ACK : ConsumerInterface::MSG_REJECT_REQUEUE;
        } catch (ClientException $clientException) {
            $this->writeln($data['uuid'], 'Http Client Exception');
            $this->getLogger()->error(
                sprintf(
                    'Http client error while sending a mail with UUID %s. (%s)',
                    $data['uuid'],
                    $clientException->getMessage()
                ),
                ['exception' => $clientException]
            );

            return ConsumerInterface::MSG_REJECT;
        } catch (ConnectException $error) {
            $this->writeln($data['uuid'], 'API timeout');
            $this->getLogger()->error(
                sprintf(
                    'Error with HTTP connection while sending a mail with UUID %s. (%s)',
                    $data['uuid'],
                    $error->getMessage()
                ),
                ['exception' => $error]
            );

            // to prevent requeuing in loop and user from receiving tens of mails
            return ConsumerInterface::MSG_ACK;
        } catch (InvalidUuidException $invalidUuidException) {
            $this->getLogger()->error('UUID is invalid format', ['exception' => $invalidUuidException]);

            return ConsumerInterface::MSG_ACK;
        } catch (MailerException $mailerException) {
            $this->getLogger()->error('Unable to send email to recipients.', ['exception' => $mailerException]);

            return ConsumerInterface::MSG_REJECT_REQUEUE;
        } catch (\Exception $error) {
            $this->getLogger()->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }

    public function setEmailRepository(EmailRepository $emailRepository): void
    {
        $this->emailRepository = $emailRepository;
    }

    public function setEmailClient(EmailClientInterface $emailClient): void
    {
        $this->emailClient = $emailClient;
    }

    protected function getEmailRepository(): EmailRepository
    {
        return $this->emailRepository;
    }

    protected function getEmailClient(): EmailClientInterface
    {
        return $this->emailClient;
    }
}
