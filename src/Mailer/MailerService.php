<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Event\MailerEvent;
use AppBundle\Mailer\Event\MailerEvents;
use AppBundle\Mailer\Exception\MailerException;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Transport\TransportInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MailerService
{
    const PAYLOAD_MAXSIZE = 50;

    private $dispatcher;
    private $transport;
    private $factory;
    private $emailTemplateService;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        TransportInterface $transport,
        EmailTemplateFactory $factory,
        EmailTemplateService $emailTemplateService
    ) {
        $this->dispatcher = $dispatcher;
        $this->transport = $transport;
        $this->factory = $factory;
        $this->emailTemplateService = $emailTemplateService;
    }

    public function sendMessage(Message $message): bool
    {
        $email = $this->factory->createFromMessage($message);

        try {
            $this->dispatcher->dispatch(MailerEvents::DELIVERY_MESSAGE, new MailerEvent($message, $email));

            // Check variables, they must match exactly what is required by the template
            // If we don't, the message will be sent to our mail provider but will never delivered and we won't know
            if ($message->isV2()) {
                $this->emailTemplateService->assertMessageVariablesAgainstTemplateVariable($message, $email);
            }

            $this->transport->sendTemplateEmail($email);
            $this->dispatcher->dispatch(MailerEvents::DELIVERY_SUCCESS, new MailerEvent($message, $email));
        } catch (MailerException $exception) {
            $this->dispatcher->dispatch(MailerEvents::DELIVERY_ERROR, new MailerEvent($message, $email, $exception));

            return false;
        }

        return true;
    }
}
