<?php

declare(strict_types=1);

namespace App\Analytics\PostHog;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Rôle : détecter la marque Renaissance depuis Request::getHost() → site enum.
 *
 * 4 marques mappées explicitement (spec docs/analytics/posthog-integration/spec.md §2.1).
 * Fail-open sur hostname non-mappé (return null + WARNING) — évite crash sur
 * admin/api/webhooks/health qui partagent le kernel Symfony.
 *
 * Réviseur : Dimitri (relecture humaine PR RE-5165).
 */
class SiteDetector
{
    private const HOSTNAME_SITE_MAP = [
        'utilisateur.parti-renaissance.fr' => 'parti-renaissance',
        'utilisateur.attalpresident.fr' => 'attalpresident',
        'utilisateur.avecgabrielattal.fr' => 'avecgabrielattal',
        'utilisateur.nouvellerepublique.fr' => 'nouvellerepublique',
    ];

    private const COOKIE_CONFIG_BY_SITE = [
        'attalpresident' => ['name' => 'ap_consent', 'domain' => '.attalpresident.fr'],
        'parti-renaissance' => ['name' => 'pr_consent', 'domain' => '.parti-renaissance.fr'],
        'avecgabrielattal' => ['name' => 'aga_consent', 'domain' => '.avecgabrielattal.fr'],
        'nouvellerepublique' => ['name' => 'nr_consent', 'domain' => '.nouvellerepublique.fr'],
    ];

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function detectFromRequest(Request $request): ?string
    {
        $host = strtolower($request->getHost());
        if (isset(self::HOSTNAME_SITE_MAP[$host])) {
            return self::HOSTNAME_SITE_MAP[$host];
        }

        $this->logger->warning(
            'PostHog SiteDetector: hostname hors périmètre PostHog Renaissance',
            ['hostname' => $host],
        );

        return null;
    }

    /** @return array{name: string, domain: string} */
    public static function getCookieConfig(string $site): array
    {
        return self::COOKIE_CONFIG_BY_SITE[$site]
            ?? throw new \InvalidArgumentException("Unknown site: $site");
    }
}
