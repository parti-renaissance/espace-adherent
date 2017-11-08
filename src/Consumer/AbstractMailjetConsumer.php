<?php

namespace AppBundle\Consumer;

use AppBundle\Exception\InvalidUuidException;
use AppBundle\Mailjet\ClientInterface;
use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Repository\MailjetEmailRepository;
use GuzzleHttp\Exception\ConnectException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AbstractMailjetConsumer extends AbstractConsumer
{
    protected $client;
    protected $mailjetEmailRepository;

    protected function configureDataConstraints(): array
    {
        return [
            'uuid' => [new Assert\NotBlank(), new Assert\Uuid()],
        ];
    }

    protected function doExecute(array $data): int
    {
        try {
            if (!$message = $this->getMailjetRepository()->findOneByUuid($data['uuid'])) {
                $this->getLogger()->error('MailjetEmail not found', $data);
                $this->writeln($data['uuid'], 'MailjetEmail not found, rejecting');

                return ConsumerInterface::MSG_ACK;
            }

            $this->writeln($data['uuid'], 'Delivering '.$message->getEnglishLog());
            $delivered = $this->getMailjetClient()->sendEmail($message->getRequestPayloadJson());

            if (!$delivered) {
                $this->writeln($data['uuid'], 'An issue occured, requeuing');
            } else {
                $this->getMailjetRepository()->setDelivered($message, $delivered);
            }

            return $delivered ? ConsumerInterface::MSG_ACK : ConsumerInterface::MSG_REJECT_REQUEUE;
        } catch (ConnectException $error) {
            $this->writeln($data['uuid'], 'API timeout');
            $this->getLogger()->error(
                'RabbitMQ connection timeout while sending a mail with UUID '.$data['uuid'],
                ['exception' => $error]
            );

            // to prevent requeuing in loop and user from receiving tens of mails
            return ConsumerInterface::MSG_ACK;
        } catch (InvalidUuidException $invalidUuidException) {
            $this->getLogger()->error('UUID is invalid format', ['exception' => $invalidUuidException]);

            return ConsumerInterface::MSG_ACK;
        } catch (MailjetException $mailjetException) {
            $this->getLogger()->error('Unable to send email to recipients.', ['exception' => $mailjetException]);

            return ConsumerInterface::MSG_REJECT_REQUEUE;
        } catch (\Exception $error) {
            $this->getLogger()->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }

    public function setMailjetRepository(MailjetEmailRepository $mailjetEmailRepository): void
    {
        $this->mailjetEmailRepository = $mailjetEmailRepository;
    }

    public function setMailjetClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    protected function getMailjetClient(): ClientInterface
    {
        return $this->client;
    }

    protected function getMailjetRepository(): MailjetEmailRepository
    {
        return $this->mailjetEmailRepository;
    }
}
