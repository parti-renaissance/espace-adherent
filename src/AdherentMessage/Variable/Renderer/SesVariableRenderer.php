<?php

declare(strict_types=1);

namespace App\AdherentMessage\Variable\Renderer;

class SesVariableRenderer implements PublicationVariableRendererInterface
{
    public static function getFormat(): string
    {
        return 'ses';
    }

    public function isContextRequired(): bool
    {
        return true;
    }

    public function render(string $content, array $variables, array $context = []): string
    {
        if (!$variables) {
            return $content;
        }

        $replacements = [];
        foreach ($variables as $variable) {
            $replacements[$variable] = $context[$variable] ?? $variable;
        }

        return strtr($content, $replacements);
    }
}
