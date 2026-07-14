<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

/**
 * Rôle : holds le site détecté pour la durée d'une requête HTTP.
 * Injecté par SiteContextListener (kernel.request priority=250).
 * Consommé par HashEmailService, ConsentCookieHelper, PostHogTwigExtension.
 *
 * `isInitialized() === false` signifie "hostname hors périmètre PostHog"
 * (admin, api, webhooks, health) — les consumers doivent gérer ce cas
 * gracefully.
 */
final class SiteContext
{
    private ?string $site = null;

    public function setSite(string $site): void
    {
        $this->site = $site;
    }

    public function getSite(): string
    {
        return $this->site ?? throw new \LogicException(
            'SiteContext::getSite() called before init. Vérifiez SiteContextListener'
            . ' registration + check isInitialized() côté consumer.',
        );
    }

    public function isInitialized(): bool
    {
        return null !== $this->site;
    }

    /** @return array{name: string, domain: string} */
    public function getCookieConfig(): array
    {
        return SiteDetector::getCookieConfig($this->getSite());
    }
}
