<?php

namespace AppBundle\Mailer\EventSubscriber;

use AppBundle\Entity\Email;
use AppBundle\Mailer\Event\MailerEvent;
use AppBundle\Mailer\Event\MailerEvents;
use AppBundle\Repository\EmailRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailPersisterEventSubscriber implements EventSubscriberInterface
{
    private $manager;
    private $repository;

    public function __construct(ObjectManager $manager, EmailRepository $repository)
    {
        $this->manager = $manager;
        $this->repository = $repository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MailerEvents::DELIVERY_MESSAGE => 'onMailerDeliveryMessage',
            MailerEvents::DELIVERY_SUCCESS => 'onMailerDeliverySuccess',
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

        $this->manager->persist($email);
        $this->manager->flush();
    }
}
