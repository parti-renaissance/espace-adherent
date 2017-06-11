<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\MailjetEmail;
use AppBundle\Mailjet\ClientInterface;
use AppBundle\Repository\MailjetEmailRepository;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Validator\Constraints as Assert;

class AbstractMailjetConsumer extends AbstractConsumer
{
    const CLIENT_ID = 'to_override';

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
            if (!$message = $this->getMailjetRepository()->findOneByUuid($data['uuid'])) {
                $logger->error('MailjetEmail not found', $data);
                $this->writeln($data['uuid'], 'MailjetEmail not found, rejecting');

                return true;
            }

            $this->writeln($data['uuid'], 'Delivering '.$message->getEnglishLog());
            $delivered = $this->getMailjetClient()->sendEmail($message->getRequestPayloadJson());

            if (!$delivered) {
                $this->writeln($data['uuid'], 'An issue occured, requeuing');
            } else {
                $this->getMailjetRepository()->setDelivered($message, $delivered);
            }

            return $delivered;
        } catch (ConnectException $error) {
            $this->writeln($data['uuid'], 'API timeout, requeuing');

            return false;
        } catch (\Exception $error) {
            $logger->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }

    protected function getMailjetClient(): ClientInterface
    {
        return $this->container->get(static::CLIENT_ID);
    }

    protected function getMailjetRepository(): MailjetEmailRepository
    {
        return $this->getDoctrine()->getRepository(MailjetEmail::class);
    }
}
