<?php

declare(strict_types=1);

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\Repository\AppAlertRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class AppAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private readonly AppAlertRepository $repository,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getAlerts(Adherent $adherent): array
    {
        if (!$appAlerts = $this->repository->findAllActive()) {
            return [];
        }

        $alerts = [];

        foreach ($appAlerts as $appAlert) {
            if ($appAlert->withMagicLink && $appAlert->ctaUrl) {
                $ctaUrl = $this->loginLinkHandler->createLoginLink($adherent, targetPath: $appAlert->ctaUrl);
            }
            $alerts[] = $alert = Alert::createFromAppAlert($appAlert, $ctaUrl ?? null);
            $alert->date = $appAlert->beginAt;
        }

        return $alerts;
    }
}
