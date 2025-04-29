<?php

namespace App\Mailer;

use App\Mailer\Message\EmailTemplateMessage;
use App\Mailer\Message\Message;
use App\Mailer\Template\Manager;
use App\Mandrill\EmailTemplate;

class EmailTemplateFactory
{
    public function __construct(
        private readonly string $senderEmail,
        private readonly string $senderName,
        private readonly Manager $templateManager,
    ) {
    }

    public function createFromMessage(Message $message): AbstractEmailTemplate
    {
        $templateObject = $this->templateManager->findTemplateForMessage($message);

        $email = new EmailTemplate(
            $message->getUuid(),
            $templateObject ? '' : ($message->getTemplate() ?? $message->generateTemplateName()),
            $templateObject && $templateObject->subject ? $templateObject->subject : $message->getSubject(),
            $message->getSenderEmail() ?: $this->senderEmail,
            $message->getSenderName() ?: $this->senderName,
            $message->getReplyTo(),
            $message->getCC(),
            $message->getBCC(),
            $templateVars = $message->getVars(),
            $message->getTemplateContent()
        );

        foreach ($message->getRecipients() as $recipient) {
            $email->addRecipient($recipient->getEmailAddress(), $recipient->getFullName(), $templateVars += $recipient->getVars());
        }

        $email->setPreserveRecipients($message->getPreserveRecipients());

        if ($templateObject) {
            $email->setMessageHtmlContent($this->templateManager->getTemplateContent($templateObject, $message instanceof EmailTemplateMessage, $templateVars));
        }

        return $email;
    }
}
