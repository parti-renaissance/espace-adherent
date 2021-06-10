<?php

namespace App\Adherent;

use App\Entity\Adherent;

class AdherentInterestsMigrationHandler
{
    private const INTERESTS_MAPPING = [
        'agriculture' => 'ruralite',
        'social' => 'solidarites',
        'culture' => 'culture',
        'economie' => 'economie',
        'education' => 'education_et_jeunesse',
        'egalite' => 'egalite',
        'emploi' => 'travail',
        'environement' => 'transition_ecologique',
        'europe' => 'europe',
        'international' => 'international',
        'jeunesse' => 'education_et_jeunesse',
        'justice' => 'justice',
        'numerique' => 'numerique',
        'sante' => 'sante',
        'securite' => 'securite',
        'sport' => 'solidarites',
        'territoire' => 'ruralite',
        'institution' => 'democratie',
    ];

    public function migrateInterests(Adherent $adherent): void
    {
        $newInterests = [];

        foreach ($adherent->getInterests() as $interest) {
            if (!\array_key_exists($interest, self::INTERESTS_MAPPING)) {
                continue;
            }

            $newInterests[] = self::INTERESTS_MAPPING[$interest];
        }

        $adherent->setInterests(array_unique($newInterests));
    }
}
