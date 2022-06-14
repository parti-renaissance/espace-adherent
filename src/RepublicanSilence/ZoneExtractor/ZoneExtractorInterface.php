<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;

interface ZoneExtractorInterface
{
    public const NONE = -1;
    public const ADHERENT_TYPE_REFERENT = 0;
    public const ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR = 2; // Supervisor or Host
    public const ADHERENT_TYPE_DEPUTY = 3; // Deputy
    public const ADHERENT_TYPE_MUNICIPAL_CHIEF = 4; // Municipal chief
    public const ADHERENT_TYPE_SENATOR = 5; // Senator
    public const ADHERENT_TYPE_PROCURATION_MANAGER = 6;

    public function extractZones(Adherent $adherent, ?string $slug): array;

    public function supports(int $type): bool;
}
