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

            // Inject style block for .hidden
            $content = preg_replace(
                '/<\/head>/i',
                "<style>.hidden { display: none !important; mso-hide: all !important; max-height: 0px !important; overflow: hidden !important; font-size: 0 !important; line-height: 0 !important; opacity: 0 !important; visibility: hidden !important; }</style>\n</head>",
                $content,
                1
            );
        }

        // Ensure lang attribute is set in <html> tag
        $content = preg_replace_callback(
            '/<html\b([^>]*)>/i',
            static fn ($matches) => str_contains($matches[1], 'lang=') ? $matches[0] : '<html lang="fr"'.$matches[1].'>',
            $content,
            1
        );

        return $content;
    }

    public function findTemplateForMessage(Message $message): ?TransactionalEmailTemplate
    {
        if ($message->getTemplateObject()) {
            return $message->getTemplateObject();
        }

        return $this->templateRepository->findOneBy(['identifier' => $message::class]);
    }

    public function findTemplateVars(string $identifier): array
    {
        try {
            $ref = new \ReflectionClass($identifier);
        } catch (\ReflectionException $e) {
            return [];
        }

        $parent = $ref->getParentClass();

        $content = file_get_contents($ref->getFileName());
        $matches = [];
        preg_match_all("/'(?<key>[a-zA-Z0-9_.]+)'\s*=>/", $content, $matches);

        return array_values(array_unique(array_merge($matches['key'] ?? [], $parent && Message::class !== $parent->getName() ? $this->findTemplateVars($parent->getName()) : [])));
    }
}
