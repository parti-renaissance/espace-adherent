<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\JeMengage\Timeline\Mirror\TimelineFeedHider;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route(path: '/timeline-feed/hide', name: 'app_timeline_feed_hide_webhook', methods: ['POST'])]
class HideTimelineFeedWebhookController extends AbstractController
{
    public function __construct(private readonly string $timelineFeedHideWebhookKey)
    {
    }

    public function __invoke(Request $request, TimelineFeedHider $hider, LoggerInterface $logger): Response
    {
        $token = (string) $request->headers->get('X-Webhook-Token', '');

        if ('' === $this->timelineFeedHideWebhookKey || !hash_equals($this->timelineFeedHideWebhookKey, $token)) {
            $logger->error('[HideTimelineFeed webhook] invalid or missing token.');

            return new JsonResponse(['error' => 'invalid token'], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        $uuid = \is_array($payload) ? ($payload['uuid'] ?? null) : null;

        if (!\is_string($uuid) || !Uuid::isValid($uuid)) {
            return new JsonResponse(['error' => 'invalid uuid'], Response::HTTP_BAD_REQUEST);
        }

        $hider->hide(Uuid::fromString($uuid));

        return new JsonResponse(['status' => 'hidden', 'uuid' => $uuid]);
    }
}
