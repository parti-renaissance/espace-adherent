<?php

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
    public const SITE_PROCURATION = 'site_procuration';
}
