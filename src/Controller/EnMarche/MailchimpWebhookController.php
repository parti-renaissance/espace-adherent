<?php

namespace App\Controller\EnMarche;

use App\Mailchimp\Webhook\EventTypeEnum;
use App\Mailchimp\Webhook\WebhookHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mailchimp/webhook/{key}", name="app_mailchimp_webhook", methods={"GET", "POST"})
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
        if ($key === $this->mailchimpWebhookKey) {
            $data = (array) $request->request->get('data', []);

            $listId = $data['list_id'] ?? null;

            if (null === $listId) {
                $logger->error('[Mailchimp Webhook] list id is not found', ['request' => $request->request->all()]);
            } elseif ($request->isMethod(Request::METHOD_POST) && EventTypeEnum::isValid($type = $request->request->get('type'))) {
                $handler->handle($type, $listId, $data);
            }
        } else {
            $logger->error(sprintf('[Mailchimp Webhook] invalid request key "%s"', $key));
        }

        return new Response('OK');
    }
}
