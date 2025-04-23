<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdherentAdminExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_donations_history', [AdherentAdminRuntime::class, 'getDonationsHistory']),
            new TwigFunction('get_subscribed_donations', [AdherentAdminRuntime::class, 'getSubscribedDonations']),
            new TwigFunction('get_last_subscription_ended', [AdherentAdminRuntime::class, 'getLastSubscriptionEnded']),
            new TwigFunction('get_tax_receipts_for_adherent', [AdherentAdminRuntime::class, 'getTaxReceiptsForAdherent']),
            new TwigFunction('get_tax_receipts_for_donator', [AdherentAdminRuntime::class, 'getTaxReceiptsForDonator']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('get_system_detail_from_user_agent', [AdherentAdminRuntime::class, 'getSystemDetailFromUserAgent']),
        ];
    }
}
