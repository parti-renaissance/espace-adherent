<?php

namespace AppBundle\Consumer;

use AppBundle\Mailjet\ClientInterface;
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
            'uuid' => [new Assert\NotBlank()],
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
            $this->getLogger()->error('RabbitMQ API timeout while sending a mail with UUID '.$data['uuid'], ['exception' => $error]);

            // to prevent requeuing in loop and user from receiving tens of mails
            return ConsumerInterface::MSG_ACK;
        } catch (\Exception $error) {
            $this->getLogger()->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }

    protected function getMailjetClient(): ClientInterface
    {
        return $this->client;
    }

    public function setMailjetClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    protected function getMailjetRepository(): MailjetEmailRepository
    {
        return $this->mailjetEmailRepository;
    }

    public function setMailjetRepository(MailjetEmailRepository $mailjetEmailRepository): void
    {
        $this->mailjetEmailRepository = $mailjetEmailRepository;
    }
}
