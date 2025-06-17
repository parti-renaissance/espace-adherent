<?php

namespace App\Controller\Webhook;

use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/ogone/update-status/{key}', name: 'app_ogone_update_status_webhook', methods: ['POST'])]
class OgoneStatusController extends AbstractController
{
    public function __construct(private readonly string $ogoneWebhookKey)
    {
    }

    public function __invoke(string $key, Request $request, MessageBusInterface $bus, LoggerInterface $logger): Response
    {
        if ($key === $this->ogoneWebhookKey && $request->isMethod(Request::METHOD_POST)) {
            $bus->dispatch(new PaymentStatusUpdateCommand($request->request->all()));
        }

        return new Response('OK');
    }
}
