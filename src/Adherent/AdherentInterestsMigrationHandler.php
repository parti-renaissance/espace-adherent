<?php

namespace App\Adherent;

use App\Entity\Adherent;

class AdherentInterestsMigrationHandler
{
    private const INTERESTS_MAPPING = [
        'culture' => 'culture',
        'institution' => 'democratie',
        'economie' => 'economie',
        'education' => 'education',
        'jeunesse' => 'jeunesse',
        'egalite' => 'egalite',
        'europe' => 'europe',
        'international' => 'international',
        'justice' => 'justice',
        'numerique' => 'numerique',
        'agriculture' => 'ruralite',
        'territoire' => 'ruralite',
        'sante' => 'sante',
        'securite' => 'securite_et_defense',
        'social' => 'solidarites',
        'sport' => 'sport',
        'environement' => 'transition_ecologique',
        'emploi' => 'travail',
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
