<?php

declare(strict_types=1);

namespace App\AdherentMessage\Variable\Renderer;

interface PublicationVariableRendererInterface
{
    public static function getFormat(): string;

    public function render(string $content, array $variables, array $context = []): string;

    public function isContextRequired(): bool;
}
