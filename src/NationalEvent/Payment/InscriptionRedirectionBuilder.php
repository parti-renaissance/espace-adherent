<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment;

use App\Entity\NationalEvent\EventInscription;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Where a payer goes once their inscription is paid. Shared by the status page and the polling endpoint so both
 * always agree on the destination.
 */
class InscriptionRedirectionBuilder
{
    public function __construct(private readonly UrlGeneratorInterface $router)
    {
    }

    public function buildConfirmationUrl(EventInscription $inscription, string $appDomain): string
    {
        $event = $inscription->event;

        if ($event->isPackageEventType()) {
            return $this->router->generate('app_national_event_my_inscription', [
                'slug' => $event->getSlug(),
                'uuid' => $inscription->getUuid()->toRfc4122(),
                'app_domain' => $appDomain,
                'confirmation' => true,
            ]);
        }

        return $this->router->generate('app_national_event_inscription_confirmation', [
            'slug' => $event->getSlug(),
            'app_domain' => $appDomain,
        ]);
    }
}
