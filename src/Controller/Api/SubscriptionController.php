<?php

namespace AppBundle\Controller\Api;

use AppBundle\Subscription\SubscriptionHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/email-subscriptions")
 */
class SubscriptionController extends Controller
{
    /**
     * @Route("/change", name="app_change_email_subscriptions_webhook")
     * @Method({"GET", "POST"})
     */
    public function changeEmailSubscriptionsAction(Request $request): Response
    {
        if (!$request->query->has('secret') || !$secret = $request->query->get('secret')) {
            throw new UnauthorizedHttpException('There is no secret');
        }

        if ($secret !== $this->container->getParameter('webhook.email_subscriptions_secret')) {
            throw new AccessDeniedHttpException('Wrong secret.');
        }

        // To allow Mailchimp or another external service to test the route accessibility with GET method
        if ($request->isMethod('GET')) {
            return new Response('OK');
        }

        $type = $request->request->get('type');
        $data = $request->request->get('data');

        if (0 === $request->request->count() || !$type || !isset($data['email']) || !isset($data['list_id'])) {
            throw new BadRequestHttpException('The request should contain correct data.');
        }

        $this->get(SubscriptionHandler::class)->changeSubscription($type, $data['email'], $data['list_id']);

        return new Response('OK');
    }
}
