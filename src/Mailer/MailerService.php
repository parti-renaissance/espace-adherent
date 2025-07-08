<?php

namespace App\Mailer;

use App\Mailer\Event\MailerEvent;
use App\Mailer\Event\MailerEvents;
use App\Mailer\Exception\MailerException;
use App\Mailer\Message\Message;
use App\Mailer\Transport\TransportInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MailerService
{
    public const PAYLOAD_MAXSIZE = 50;

    private $dispatcher;
    private $transport;
    private $factory;
    private $emailClient;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        TransportInterface $transport,
        EmailTemplateFactory $factory,
        EmailClientInterface $emailClient,
    ) {
        $this->dispatcher = $dispatcher;
        $this->transport = $transport;
        $this->factory = $factory;
        $this->emailClient = $emailClient;
    }

    public function sendMessage(Message $message, bool $async = true): bool
    {
        $this->dispatcher->dispatch(new MailerEvent($message), MailerEvents::BEFORE_EMAIL_BUILD);

        $email = $this->factory->createFromMessage($message);

        $delivered = true;
        try {
            $this->dispatcher->dispatch(new MailerEvent($message, $email), MailerEvents::DELIVERY_MESSAGE);
            $this->transport->sendTemplateEmail($email, $async);
            $this->dispatcher->dispatch(new MailerEvent($message, $email), MailerEvents::DELIVERY_SUCCESS);
        } catch (MailerException $exception) {
            $delivered = false;
            $this->dispatcher->dispatch(new MailerEvent($message, $email, $exception), MailerEvents::DELIVERY_ERROR);
        }

        return $delivered;
    }
}
