<?php

declare(strict_types=1);

namespace App\Analytics\PostHog;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * Rôle : lecture/écriture du cookie consent scopé root-domain par marque.
 *
 * Cookies par marque (partagés cross-sous-domaines de la même marque) :
 *   .parti-renaissance.fr   → pr_consent
 *   .attalpresident.fr      → ap_consent (déjà en prod attalpresident.fr,
 *                                        migration idempotente obligatoire)
 *   .avecgabrielattal.fr    → aga_consent
 *   .nouvellerepublique.fr  → nr_consent
 *
 * Valeurs : '1' = granted, '0' = refused, absent = undefined.
 * Attributes : Secure, SameSite=Lax, HttpOnly=false (JS doit lire pour SDK PostHog).
 *
 * Cf. spec §5.
 */
class ConsentCookieHelper
{
    private const MAX_AGE_SECONDS = 34_128_000; // ~13 mois (CNIL max)

    public function __construct(private readonly SiteContext $context)
    {
    }

    public function read(Request $request): ?bool
    {
        $config = $this->context->getCookieConfig();
        $raw = $request->cookies->get($config['name']);

        return match ($raw) {
            '1' => true,
            '0' => false,
            default => null,
        };
    }

    public function write(bool $granted): Cookie
    {
        $config = $this->context->getCookieConfig();

        return Cookie::create(
            name: $config['name'],
            value: $granted ? '1' : '0',
            expire: time() + self::MAX_AGE_SECONDS,
            path: '/',
            domain: $config['domain'],
            secure: true,
            httpOnly: false,
            sameSite: Cookie::SAMESITE_LAX,
        );
    }
}
