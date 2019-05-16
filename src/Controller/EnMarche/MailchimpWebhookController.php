<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Mailchimp\Webhook\WebhookHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mailchimp/webhook/{key}", name="app_mailchimp_webhook", methods={"POST"})
 */
class MailchimpWebhookController extends Controller
{
    private $mailchimpWebhookKey;

    public function __construct(string $mailchimpWebhookKey)
    {
        $this->mailchimpWebhookKey = $mailchimpWebhookKey;
    }

    public function __invoke(string $key, Request $request, WebhookHandler $handler, LoggerInterface $logger): Response
    {
        $data = $request->request->all();

        if ($key === $this->mailchimpWebhookKey) {
            if (EventTypeEnum::isValid($request->request->get('type'))) {
                $handler($data['type'], $data['data'] ?? []);
            }
        } else {
            $logger->error(sprintf('[Mailchimp Webhook] invalid request key "%s"', $key), ['request_data' => $data, 'request' => $request]);
        }

        return new Response('OK');
    }
}
