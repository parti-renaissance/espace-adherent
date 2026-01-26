<?php

declare(strict_types=1);

namespace App\Newsletter;

use MyCLabs\Enum\Enum;

class NewsletterTypeEnum extends Enum
{
    public const MAIN_SITE = 'main_site';
    public const MAIN_SITE_FROM_EVENT = 'main_site_from_event';
    public const SITE_DEPARTMENTAL = 'site_departmental';
    public const SITE_MUNICIPAL = 'site_municipal';
    public const SITE_LEGISLATIVE_CANDIDATE = 'site_legislative_candidate';
    public const SITE_RENAISSANCE = 'site_renaissance';
    public const SITE_EU = 'site_eu';
    public const SITE_NRP = 'nouvelle-république';
    public const SITE_ENSEMBLE = 'site_ensemble';
    public const FROM_EVENT = 'from_event';
    public const FROM_MEETING = 'from_meeting';
    public const SITE_PROCURATION = 'site_procuration';
    public const SITE_STOPRESEAUX = 'site_stopreseaux';
}
