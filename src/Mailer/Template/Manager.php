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

    public function getTemplateContent(TransactionalEmailTemplate $template, bool $fillVariables = false, array $templateVars = []): string
    {
        $content = $template->getContent();

        if ($template->parent) {
            preg_match('/<body[^>]*>([\s\S]*?)<\/body>/i', $template->getContent(), $matches);
            $childContent = $matches[1];

            $content = preg_replace('/(<table\s+[^>]*class="template-email-block"[^>]*>)([\s\S]*?<div[^>]*>)[\s\S]*?(<\/div>[\s\S]*?<\/table>)/i', '$1$2'.$childContent.'$3', $template->parent->getContent());
        }

        if ($fillVariables) {
            $content = preg_replace_callback('/\{{2,3}\s*(\w+)\s*}{2,3}/', fn ($matches) => $matches[1], $content);
        }

        if ($templateVars) {
            $content = preg_replace_callback(
                '/class="([^"]*?\bshow_if:(\w+)\b[^"]*?)"/',
                static function ($matches) use ($templateVars) {
                    [, $fullClass, $key] = $matches;

                    if (!isset($templateVars[$key]) || !$templateVars[$key]) {
                        $newClass = preg_replace('/\bshow_if:'.preg_quote($key, '/').'\b/', 'hidden', $fullClass);

                        return 'class="'.$newClass.'"';
                    }

                    return $matches[0];
                },
                $content
            );
        }

        return $content;
    }

    public function findTemplateForMessage(Message $message): ?TransactionalEmailTemplate
    {
        if ($message->getTemplateObject()) {
            return $message->getTemplateObject();
        }

        return $this->templateRepository->findOneBy(['identifier' => $message::class]);
    }
}
