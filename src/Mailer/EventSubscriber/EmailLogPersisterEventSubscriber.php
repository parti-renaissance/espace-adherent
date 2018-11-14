<?php

namespace AppBundle\Mailer\EventSubscriber;

use AppBundle\Entity\EmailLog;
use AppBundle\Mailer\Event\LogMailerEvents;
use AppBundle\Mailer\Event\MailerEvent;
use AppBundle\Repository\EmailLogRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailLogPersisterEventSubscriber implements EventSubscriberInterface
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
            LogMailerEvents::DELIVERY_MESSAGE => 'onMailerDeliveryMessage',
        ];
    }

    public function onMailerDeliveryMessage(MailerEvent $event): void
    {
        $message = $event->getMessage();

        if ($message->getReplyTo()) {
            $email = EmailLog::createFromMessage($message);

            $this->manager->persist($email);
            $this->manager->flush();
            $this->manager->detach($email);
        }
    }
}
