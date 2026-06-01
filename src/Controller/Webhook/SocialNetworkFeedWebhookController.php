<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\SocialNetwork\Webhook\Command\SocialNetworkFeedWebhookCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/social-network-feed/{key}', name: 'app_social_network_feed_webhook', methods: ['POST'])]
class SocialNetworkFeedWebhookController extends AbstractController
{
    public function __construct(private readonly string $socialNetworkFeedWebhookKey)
    {
    }

    public function __invoke(string $key, Request $request, MessageBusInterface $bus, LoggerInterface $logger): Response
    {
        if ($key !== $this->socialNetworkFeedWebhookKey) {
            $logger->error(\sprintf('[SocialNetworkFeed webhook] invalid request key "%s".', $key), ['request_body' => $request->getContent()]);

            return new Response('OK');
        }

        $payload = json_decode($request->getContent(), true);

        if (!\is_array($payload)) {
            $logger->error('[SocialNetworkFeed webhook] invalid JSON body.', ['request_body' => $request->getContent()]);

            return new Response('OK');
        }

        $bus->dispatch(new SocialNetworkFeedWebhookCommand($payload));

        return new Response('OK');
    }
}
