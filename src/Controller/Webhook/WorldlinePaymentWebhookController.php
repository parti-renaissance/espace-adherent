<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Payment\Worldline\WorldlineWebhookVerifier;
use OnlinePayments\Sdk\Webhooks\ApiVersionMismatchException;
use OnlinePayments\Sdk\Webhooks\SignatureValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/worldline/{key}', name: 'app_worldline_payment_webhook', methods: ['POST'])]
class WorldlinePaymentWebhookController extends AbstractController
{
    public function __construct(
        private readonly string $worldlineWebhookUrlKey,
        private readonly string $worldlineMerchantId,
    ) {
    }

    public function __invoke(
        string $key,
        Request $request,
        WorldlineWebhookVerifier $webhookVerifier,
        MessageBusInterface $bus,
        LoggerInterface $logger,
    ): Response {
        // Every path answers 200: Worldline retries on anything else, and a caller must not be able to tell a wrong
        // url key from a wrong signature.
        if (!hash_equals($this->worldlineWebhookUrlKey, $key)) {
            $logger->error('Worldline webhook rejected, unexpected url key.');

            return new Response('OK');
        }

        try {
            // getContent() must stay raw: any re-encoding invalidates the signature.
            $event = $webhookVerifier->verifyAndExtract($request->getContent(), $request->headers->all());
        } catch (SignatureValidationException|ApiVersionMismatchException $e) {
            $logger->error('Rejected Worldline webhook: {reason}.', ['reason' => $e->getMessage()]);

            return new Response('OK');
        }

        if (null === $event) {
            return new Response('OK');
        }

        if (null !== $event->merchantId && !hash_equals($this->worldlineMerchantId, $event->merchantId)) {
            $logger->error('Worldline webhook rejected, unexpected merchant {merchantId}.', ['merchantId' => $event->merchantId]);

            return new Response('OK');
        }

        $bus->dispatch(new PaymentStatusUpdateCommand($event->payment));

        return new Response('OK');
    }
}
