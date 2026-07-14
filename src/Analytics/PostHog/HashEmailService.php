<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

/**
 * Rôle : hash SHA256("SALT:email_norm") avec salt marque-specific.
 *
 * Format : SHA256("${SALT}:${email_norm}") — SALT devant, ':' séparateur,
 * email_norm = trim + strtolower.
 *
 * Byte-identique à attalpresident.fr/lib/posthog/identity.ts L22-38.
 * Divergence intentionnelle vs doctrine cross-sites §4.2 (salt global) —
 * cf. spec docs/analytics/posthog-integration/spec.md §2.4.
 *
 * Cross-brand PostHog UI : 2 persons distinctes web/marque pour un même
 * adhérent — réconciliation via view BQ `posthog_identity_bridge` mart
 * Renaissance (crm-integrations PR #175).
 */
final class HashEmailService
{
    /** @param array<string, string> $saltsBySite Map site → salt marque-specific */
    public function __construct(
        private readonly SiteContext $context,
        private readonly array $saltsBySite,
    ) {}

    public function hash(string $email): string
    {
        $normalized = strtolower(trim($email));
        if ('' === $normalized) {
            throw new \InvalidArgumentException('HashEmailService: email vide ou whitespace-only');
        }
        $site = $this->context->getSite();
        $salt = $this->saltsBySite[$site]
            ?? throw new \RuntimeException("HashEmailService: no salt configured for site '$site'");
        return hash('sha256', $salt . ':' . $normalized);
    }
}
