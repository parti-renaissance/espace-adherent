<?php

namespace AppBundle\Mailjet\EventSubscriber;

use AppBundle\Entity\MailjetEmail;
use AppBundle\Mailjet\Event\MailjetEvent;
use AppBundle\Mailjet\Event\MailjetEvents;
use AppBundle\Repository\MailjetEmailRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailPersisterEventSubscriber implements EventSubscriberInterface
{
    private $manager;
    private $repository;

    public function __construct(ObjectManager $manager, MailjetEmailRepository $repository)
    {
        $this->manager = $manager;
        $this->repository = $repository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MailjetEvents::DELIVERY_MESSAGE => 'onMailjetDeliveryMessage',
            MailjetEvents::DELIVERY_SUCCESS => 'onMailjetDeliverySuccess',
        ];
    }

    public function onMailjetDeliveryMessage(MailjetEvent $event): void
    {
        $email = $event->getEmail();
        $message = $event->getMessage();

        $this->manager->persist(MailjetEmail::createFromMessage($message, $email->getHttpRequestPayload()));
        $this->manager->flush();
    }

    public function onMailjetDeliverySuccess(MailjetEvent $event): void
    {
        $templateEmail = $event->getEmail();

        if (!$responsePayload = $templateEmail->getHttpResponsePayload()) {
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
