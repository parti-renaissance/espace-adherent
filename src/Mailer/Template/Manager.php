<?php

namespace App\Mailer\Template;

use App\Entity\Email\TransactionalEmailTemplate;
use App\Mailer\Message\Message;
use App\Repository\Email\TransactionalEmailTemplateRepository;

class Manager
{
    public function __construct(private readonly TransactionalEmailTemplateRepository $templateRepository)
    {
    }

    public function getTemplateContent(TransactionalEmailTemplate $template): string
    {
        if ($template->parent) {
            preg_match('/<body[^>]*>([\s\S]*?)<\/body>/i', $template->getContent(), $matches);
            $childContent = $matches[1];

            return preg_replace('/(<table\s+[^>]*class="template-email-block"[^>]*>)([\s\S]*?<div[^>]*>)[\s\S]*?(<\/div>[\s\S]*?<\/table>)/i', '$1$2'.$childContent.'$3', $template->parent->getContent());
        }

        return $template->getContent();
    }

    public function findTemplateForMessage(Message $message): ?TransactionalEmailTemplate
    {
        if ($message->getTemplateObject()) {
            return $message->getTemplateObject();
        }

        return $this->templateRepository->findOneBy(['identifier' => $message::class]);
    }
}
