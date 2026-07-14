<?php

declare(strict_types=1);

namespace App\Analytics\PostHog\Twig;

use App\Analytics\PostHog\ConsentCookieHelper;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
use App\Analytics\PostHog\SiteContext;
use App\Entity\Adherent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

/**
 * Expose des variables Twig globales + fonctions pour le rendering des snippets PostHog :
 *   {{ posthog_config_enabled }}          → feature flag POSTHOG_ENABLED
 *   {{ posthog_config_api_key }}          → clé PostHog (via env)
 *   {{ posthog_consent_state }}           → bool|null lu du cookie
 *   {{ posthog_site }}                    → site marque courante
 *   {{ posthog_consent_cookie_name }}     → ap_consent / pr_consent / ...
 *   {{ posthog_consent_cookie_domain }}   → .attalpresident.fr / ...
 *   {{ posthog_super_properties()|json_encode|raw }}
 *   {{ posthog_identify_payload(app.user)|json_encode|raw }}
 *
 * Gère `SiteContext::isInitialized() === false` (admin/api/webhooks) sans
 * crash — retourne tous les globals à false/null/empty (review Opus C6).
 */
final class PostHogTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly SiteContext $context,
        private readonly PostHogService $service,
        private readonly HashEmailService $hashEmail,
        private readonly ConsentCookieHelper $cookieHelper,
        private readonly RequestStack $requestStack,
        #[Autowire('%posthog.enabled%')]
        private readonly bool $enabled,
        #[Autowire('%posthog.api_key%')]
        private readonly string $apiKey,
    ) {
    }

    /** @return array<string, mixed> */
    public function getGlobals(): array
    {
        if (!$this->context->isInitialized()) {
            // Hors périmètre PostHog Renaissance (admin/api/webhooks/health)
            return [
                'posthog_config_enabled' => false,
                'posthog_config_api_key' => '',
                'posthog_consent_state' => null,
                'posthog_site' => null,
                'posthog_consent_cookie_name' => null,
                'posthog_consent_cookie_domain' => null,
            ];
        }
        $config = $this->context->getCookieConfig();
        $request = $this->requestStack->getCurrentRequest();
        $consentState = null !== $request ? $this->cookieHelper->read($request) : null;

        return [
            'posthog_config_enabled' => $this->enabled,
            'posthog_config_api_key' => $this->apiKey,
            'posthog_consent_state' => $consentState,
            'posthog_site' => $this->context->getSite(),
            'posthog_consent_cookie_name' => $config['name'],
            'posthog_consent_cookie_domain' => $config['domain'],
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('posthog_super_properties', $this->superProperties(...)),
            new TwigFunction('posthog_identify_payload', $this->identifyPayload(...)),
        ];
    }

    /** @return array<string, mixed> */
    public function superProperties(): array
    {
        return $this->service->buildSuperProperties();
    }

    /** @return array<string, mixed>|null */
    public function identifyPayload(?Adherent $user): ?array
    {
        if (null === $user || null === $user->getEmailAddress()) {
            return null;
        }

        return [
            'distinct_id' => $this->hashEmail->hash($user->getEmailAddress()),
            '$set' => [
                'public_id' => $user->getPublicId(),
            ],
            '$set_once' => [
                'identified_from_site' => $this->context->getSite(),
                'identified_at' => new \DateTimeImmutable()->format(\DATE_ATOM),
            ],
        ];
    }
}
