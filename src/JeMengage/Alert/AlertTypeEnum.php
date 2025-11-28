<?php

declare(strict_types=1);

namespace App\JeMengage\Alert;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum AlertTypeEnum: string implements TranslatableInterface
{
    case ALERT = 'alert';
    case ELECTION = 'election';
    case LIVE = 'live';
    case LIVE_ANNOUNCE = 'live_announce';
    case MEETING = 'meeting';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('alert.type.'.strtolower($this->name), locale: $locale);
    }
}
