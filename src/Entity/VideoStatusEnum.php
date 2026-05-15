<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum VideoStatusEnum: string implements TranslatableInterface
{
    case PENDING = 'PENDING';
    case PROCESSING = 'PROCESSING';
    case READY = 'READY';
    case FAILED = 'FAILED';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('video.status.'.strtolower($this->name), locale: $locale);
    }
}
