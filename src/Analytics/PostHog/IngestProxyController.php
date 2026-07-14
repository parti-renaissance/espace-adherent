<?php declare(strict_types=1);

namespace App\Analytics\PostHog;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Reverse proxy /ingest/{path} → eu.i.posthog.com/{path}.
 *
 * Contourne Safari ITP / Firefox ETP : PostHog reçu en first-party sur le
 * domain marque (cookies `ph_*` posables). Whitelist paths PostHog v1.180+ :
 * e|decide|s|static|batch|array|flags|surveys|warehouse (déclarés dans
 * config/routes/analytics.yaml requirements).
 *
 * Sanitize headers upstream (jamais forward Cookie/Auth), sanitize
 * downstream (drop Set-Cookie mais garde Content-Encoding pour gzip).
 * Timeout 5s → 504 sur PostHog EU lent.
 * Rate limit 600/min keyed par IP (RateLimiterFactory `posthog_ingest`).
 *
 * Cf. spec §6, review Opus C5 (rate limiter).
 */
final class IngestProxyController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%posthog.api_host%')]
        private readonly string $apiHost,
        private readonly LoggerInterface $logger,
        #[Autowire(service: 'limiter.posthog_ingest')]
        private readonly RateLimiterFactory $rateLimiter,
    ) {}

    public function __invoke(string $path, Request $request): Response
    {
        // Rate limit keyed par IP client
        $limiter = $this->rateLimiter->create($request->getClientIp() ?? 'unknown');
        if (!$limiter->consume(1)->isAccepted()) {
            return new Response('', 429, ['Retry-After' => '60']);
        }

        $target = sprintf('%s/%s', $this->apiHost, $path);

        try {
            $upstream = $this->httpClient->request(
                $request->getMethod(),
                $target,
                [
                    'query'   => $request->query->all(),
                    'body'    => $request->getContent(),
                    'headers' => $this->forwardableHeaders($request),
                    'timeout' => 5.0,
                    'max_redirects' => 0,
                ],
            );
            $content = $upstream->getContent(throw: false);
            $status  = $upstream->getStatusCode();
            $headers = $this->sanitizeResponseHeaders($upstream->getHeaders(throw: false));
        } catch (TransportException $e) {
            $this->logger->warning('PostHog proxy transport error', [
                'exception' => $e->getMessage(),
                'path' => $path,
            ]);
            return new Response('', 504);
        }

        return new Response($content, $status, $headers);
    }

    /** @return array<string, string> */
    private function forwardableHeaders(Request $request): array
    {
        return [
            'User-Agent'      => $request->headers->get('User-Agent', ''),
            'Content-Type'    => $request->headers->get('Content-Type', 'application/json'),
            'Accept'          => $request->headers->get('Accept', '*/*'),
            'Accept-Encoding' => $request->headers->get('Accept-Encoding', 'gzip'),
        ];
    }

    /**
     * @param array<string, list<string>> $headers
     * @return array<string, list<string>>
     */
    private function sanitizeResponseHeaders(array $headers): array
    {
        // Drop uniquement : Set-Cookie (PostHog EU peut renvoyer un cookie session
        // qu'on ne veut pas laisser passer côté marque) et Transfer-Encoding (géré
        // par Symfony). GARDE Content-Encoding pour que le browser puisse décompresser
        // le gzip upstream correctement (M3 review Opus).
        unset(
            $headers['set-cookie'],
            $headers['x-posthog-set-cookie'],
            $headers['posthog-session-cookie'],
            $headers['transfer-encoding'],
        );
        return $headers;
    }
}
