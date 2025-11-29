<?php

declare(strict_types=1);

namespace App\Mailer\EventSubscriber;

use App\Entity\Email\EmailLog;
use App\Mailer\Event\MailerEvent;
use App\Mailer\Event\MailerEvents;
use App\Repository\Email\EmailLogRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailPersisterEventSubscriber implements EventSubscriberInterface
{
    private $manager;
    private $repository;

    public function __construct(ObjectManager $manager, EmailLogRepository $repository)
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

        $email = EmailLog::createFromMessage($message, $emailTemplate->getHttpRequestPayload(), $emailTemplate->fromTemplate());

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
