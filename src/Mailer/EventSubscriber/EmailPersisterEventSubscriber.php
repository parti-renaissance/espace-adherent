<?php

namespace AppBundle\Mailer\EventSubscriber;

use AppBundle\Mailer\Event\MailerEvent;
use AppBundle\Mailer\Event\MailerEvents;
use AppBundle\Mailer\Exception\MailerException;
use AppBundle\Mailer\Model\EmailRepository;
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
        $email = $event->getEmail();
        $message = $event->getMessage();

        $callable = [$this->repository->getClassName(), 'createFromMessage'];

        if (!is_callable($callable)) {
            throw new MailerException(sprintf(
                'The static method "createFromMessage" should exist in the "%s" class.',
                $this->repository->getClassName()
            ));
        }

        $this->manager->persist(call_user_func_array($callable, [
            $message,
            $email->getHttpRequestPayload(),
        ]));

        $this->manager->flush();
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
