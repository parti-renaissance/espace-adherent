<?php

namespace App\Controller\EnMarche;

use App\SmsCampaign\Command\CatchOvhSmsWebhookCallCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ovh/sms-webhook/{key}", name="app_ovh_sms_webhook", methods={"GET", "POST"})
 */
class OvhSmsWebhookController extends AbstractController
{
    private string $ovhSmsWebhookKey;

    public function __construct(string $ovhSmsWebhookKey)
    {
        $this->ovhSmsWebhookKey = $ovhSmsWebhookKey;
    }

    public function __invoke(string $key, Request $request, MessageBusInterface $bus): Response
    {
        if ($key === $this->ovhSmsWebhookKey) {
            $bus->dispatch(new CatchOvhSmsWebhookCallCommand(
                $request->getMethod(),
                $request->headers->all(),
                $request->query->all(),
                $request->request->all(),
                $request->getContent()
            ));
        }

        return new Response('OK');
    }
}
