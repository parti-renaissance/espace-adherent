<?php

namespace App\Newsletter;

use MyCLabs\Enum\Enum;

class NewsletterTypeEnum extends Enum
{
    public const MAIN_SITE = 'main_site';
    public const MAIN_SITE_FROM_EVENT = 'main_site_from_event';
    public const SITE_DEPARTMENTAL = 'site_departmental';
    public const SITE_MUNICIPAL = 'site_municipal';
}
