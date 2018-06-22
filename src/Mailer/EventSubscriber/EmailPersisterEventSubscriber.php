<?php

namespace AppBundle\Mailer\EventSubscriber;

use AppBundle\Entity\Email;
use AppBundle\Mailer\Event\MailerEvent;
use AppBundle\Mailer\Event\MailerEvents;
use AppBundle\Repository\EmailRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailPersisterEventSubscriber implements EventSubscriberInterface
{
    private $manager;
    private $repository;
    private $logger;

    public function __construct(ObjectManager $manager, EmailRepository $repository, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MailerEvents::DELIVERY_MESSAGE => 'onMailerDeliveryMessage',
            MailerEvents::DELIVERY_SUCCESS => 'onMailerDeliverySuccess',
            MailerEvents::DELIVERY_ERROR => 'onMailerDeliveryError',
        ];
    }

    public function onMailerDeliveryMessage(MailerEvent $event): void
    {
        $emailTemplate = $event->getEmail();
        $message = $event->getMessage();

        $email = Email::createFromMessage($message, $emailTemplate->getHttpRequestPayload());

        $this->manager->persist($email);
        $this->manager->flush();
        $this->manager->detach($email);
    }

    public function onMailerDeliverySuccess(MailerEvent $event): void
    {
        $emailTemplate = $event->getEmail();

        if (!$responsePayload = $emailTemplate->getHttpResponsePayload()) {
            return;
        }

        $message = $event->getMessage();
        if (!$email = $this->repository->findOneByUuid($message->getUuid()->toString())) {
            return;
        }

        $email->delivered($responsePayload);

        $this->manager->flush();
    }

    public function onMailerDeliveryError(MailerEvent $event): void
    {
        $emailTemplate = $event->getEmail();

        $this->logger->error(
            sprintf('%s email failed to be sent', $event->getEmail()->getUuid()->toString()),
            ['exception' => $event->getException()]
        );

        $message = $event->getMessage();
        if (!$email = $this->repository->findOneByUuid($message->getUuid()->toString())) {
            return;
        }

        $email->failed($event->getException(), $emailTemplate->getHttpResponsePayload());

        $this->manager->flush();
    }
}
