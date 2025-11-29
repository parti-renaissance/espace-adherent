<?php

declare(strict_types=1);

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
            $payloadDisplay = $request->request->all();

            array_walk_recursive($payloadDisplay, static function (&$v): void {
                if (!\is_string($v)) {
                    return;
                }

                if (!mb_check_encoding($v, 'UTF-8')) {
                    $v = mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1,Windows-1252,UTF-8');
                }

                $v = iconv('UTF-8', 'UTF-8//IGNORE', $v) ?: '';

                $v = str_replace("\u{FFFD}", '', $v);
            });

            $bus->dispatch(new PaymentStatusUpdateCommand($payloadDisplay));
        }

        return new Response('OK');
    }
}
