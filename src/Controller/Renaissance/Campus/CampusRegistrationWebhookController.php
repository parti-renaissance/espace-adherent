<?php

namespace App\Controller\Renaissance\Campus;

use App\Campus\Command\CatchCampusRegistrationWebhookCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/campus-registration/webhook/{key}', name: 'app_renaissance_campus_registration_webhook', methods: ['GET', 'POST'])]
class CampusRegistrationWebhookController extends AbstractController
{
    public function __construct(private readonly string $eventmakerWebhookKey)
    {
    }

    public function __invoke(Request $request, string $key, MessageBusInterface $bus, LoggerInterface $logger): Response
    {
        if ($key === $this->eventmakerWebhookKey && $request->isMethod(Request::METHOD_POST)) {
            $bus->dispatch(new CatchCampusRegistrationWebhookCommand(json_decode($request->getContent(), true)));
        } else {
            $logger->error(sprintf('[Eventmaker Webhook] invalid webhook key "%s"', $key));
        }

        return new Response('ok');
    }
}
