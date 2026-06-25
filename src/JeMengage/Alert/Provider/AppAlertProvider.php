<?php

declare(strict_types=1);

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\Entity\AppAlert;
use App\JeMengage\Alert\Alert;
use App\Repository\AppAlertRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

class AppAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private readonly AppAlertRepository $repository,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UploaderHelperInterface $uploaderHelper,
    ) {
    }

    public function getAlerts(?Adherent $adherent): array
    {
        if (null === $adherent) {
            return [];
        }

        if (!$appAlerts = $this->repository->findAllActive()) {
            return [];
        }

        $alerts = [];

        foreach ($appAlerts as $appAlert) {
            $ctaUrl = null;

            if ($appAlert->withMagicLink && $appAlert->ctaUrl) {
                $ctaUrl = $this->loginLinkHandler->createLoginLink($adherent, targetPath: $appAlert->ctaUrl)->getUrl();
            }
            $alerts[] = $alert = Alert::createFromAppAlert($appAlert, $ctaUrl ?? null, $this->getImageUrl($appAlert));
            $alert->date = $appAlert->endAt;
        }

        return $alerts;
    }

    private function getImageUrl(AppAlert $appAlert): ?string
    {
        if (!$appAlert->image?->getName()) {
            return $appAlert->imageUrl;
        }

        return $this->urlGenerator->generate(
            'asset_url',
            ['path' => str_replace('/assets/', '', $this->uploaderHelper->asset($appAlert->image))],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
