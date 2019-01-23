<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Event\MailerEvent;
use AppBundle\Mailer\Event\MailerEvents;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Exception\MailerException;
use AppBundle\Mailer\Transport\TransportInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MailerService
{
    public const PAYLOAD_MAXSIZE = 50;

    private $dispatcher;
    private $transport;
    private $factory;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        TransportInterface $transport,
        EmailTemplateFactory $factory
    ) {
        $this->dispatcher = $dispatcher;
        $this->transport = $transport;
        $this->factory = $factory;
    }

    public function sendMessage(Message $message): bool
    {
        $delivered = true;
        $email = $this->factory->createFromMessage($message);

        try {
            $this->dispatcher->dispatch(MailerEvents::DELIVERY_MESSAGE, new MailerEvent($message, $email));
            $this->transport->sendTemplateEmail($email);
            $this->dispatcher->dispatch(MailerEvents::DELIVERY_SUCCESS, new MailerEvent($message, $email));
        } catch (MailerException $exception) {
            $delivered = false;
            $this->dispatcher->dispatch(MailerEvents::DELIVERY_ERROR, new MailerEvent($message, $email, $exception));
        }

        return $delivered;
    }
}
