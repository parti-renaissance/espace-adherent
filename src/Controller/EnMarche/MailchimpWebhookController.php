<?php

namespace App\Controller\EnMarche;

use App\Mailchimp\Webhook\Command\CatchMailchimpWebhookCallCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\SerializerStamp;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mailchimp/webhook/{key}", name="app_mailchimp_webhook", methods={"GET", "POST"})
 */
class MailchimpWebhookController extends AbstractController
{
    private string $mailchimpWebhookKey;

    public function __construct(string $mailchimpWebhookKey)
    {
        $this->mailchimpWebhookKey = $mailchimpWebhookKey;
    }

    public function __invoke(string $key, Request $request, MessageBusInterface $bus, LoggerInterface $logger): Response
    {
        if ($key === $this->mailchimpWebhookKey && $request->isMethod(Request::METHOD_POST)) {
            $bus->dispatch(new CatchMailchimpWebhookCallCommand($request->request->all()), [
                new SerializerStamp(['groups' => ['command_read']]),
            ]);
        } else {
            $logger->error(sprintf('[Mailchimp Webhook] invalid request key "%s"', $key));
        }

        return new Response('OK');
    }
}
