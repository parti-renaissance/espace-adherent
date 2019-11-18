<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Message\Message;

class EmailTemplateFactory
{
    private $senderEmail;
    private $senderName;
    private $templateClass;

    public function __construct(string $senderEmail, string $senderName, string $templateClass)
    {
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->templateClass = $templateClass;
    }

    public function createFromMessage(Message $message): EmailTemplate
    {
        $callable = [$this->templateClass, 'createWithMessage'];

        if (!\is_callable($callable)) {
            throw new \LogicException(sprintf('The static method "createWithMessage" should exist in the "%s" class.', $this->templateClass));
        }

        return \call_user_func_array($callable, [
            $message,
            $this->senderEmail,
            $this->senderName,
        ]);
    }
}
