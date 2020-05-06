<?php

namespace App\RepublicanSilence\TagExtractor;

use App\Entity\Adherent;

interface ReferentTagExtractorInterface
{
    public const NONE = -1;
    public const ADHERENT_TYPE_REFERENT = 0;
    public const ADHERENT_TYPE_CITIZEN_PROJECT_ADMINISTRATOR = 1; // Host
    public const ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR = 2; // Supervisor or Host
    public const ADHERENT_TYPE_DEPUTY = 3; // Deputy
    public const ADHERENT_TYPE_MUNICIPAL_CHIEF = 4; // Municipal chief
    public const ADHERENT_TYPE_SENATOR = 5; // Senator;

    public function extractTags(Adherent $adherent, ?string $slug): array;
}
