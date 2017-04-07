<?php

namespace AppBundle\Consumer\Mailjet;

use AppBundle\Consumer\AbstractConsumer;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\MailjetMessage;
use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractMailjetConsumer extends AbstractConsumer
{
    abstract protected function getName(): string;

    abstract protected function getMailjet(): MailjetService;

    protected function getLogger(): LoggerInterface
    {
        return $this->container->get('logger');
    }

    protected function configureDataConstraints(): array
    {
        return [
            'uuid' => [new Assert\NotBlank()],
            'message' => [new Assert\NotBlank()],
        ];
    }

    public function doExecute(array $data): bool
    {
        $logger = $this->getLogger();
        $mailjet = $this->getMailjet();

        try {
            /** @var MailjetMessage $message */
            $message = @unserialize($data['message']);

            if (!$message) {
                $logger->error('Message not unserializable', $data);
                $this->writeln($this->getName(), 'Message not unserializable');

                return true;
            }

            $this->writeln($this->getName(), 'Sending email "'.$message->getSubject().'" ('.$message->getUuid()->toString().')');
            $delivered = $mailjet->sendMessage($message);

            if (!$delivered) {
                $this->writeln($this->getName(), 'An issue occured, requeuing');
            }

            return $delivered;
        } catch (ConnectException $error) {
            $this->writeln($this->getName(), 'API timeout, requeuing');

            return false;
        } catch (\Exception $error) {
            $logger->error('Consumer mailjet-campaign failed', ['exception' => $error]);

            throw $error;
        }
    }
}
