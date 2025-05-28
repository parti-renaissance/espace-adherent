<?php

namespace App\Event;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EventVisibilityEnum: string implements TranslatableInterface
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case ADHERENT = 'adherent';
    case ADHERENT_DUES = 'adherent_dues';
    case INVITATION_AGORA = 'invitation_agora';

    public static function isForAdherent(string $visibility): bool
    {
        return \in_array($visibility, [self::ADHERENT->value, self::ADHERENT_DUES->value]);
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('event.visibility.'.strtolower($this->name), locale: $locale);
    }
}
