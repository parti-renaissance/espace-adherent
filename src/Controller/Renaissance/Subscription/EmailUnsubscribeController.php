<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Subscription;

use App\Ses\Unsubscribe\UnsubscribeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/desabonnement/{token}',
    name: 'app_renaissance_email_unsubscribe',
    requirements: ['token' => '[A-Za-z0-9._-]+'],
    methods: ['GET', 'POST'],
)]
class EmailUnsubscribeController extends AbstractController
{
    public function __construct(private readonly UnsubscribeManager $unsubscribeManager)
    {
    }

    public function __invoke(Request $request, string $token): Response
    {
        $context = $this->unsubscribeManager->resolve($token);

        if (null === $context) {
            if ($request->isMethod('POST')) {
                return new Response('', Response::HTTP_BAD_REQUEST);
            }

            return $this->render(
                'renaissance/subscription/unsubscribe_error.html.twig',
                [],
                new Response('', Response::HTTP_NOT_FOUND)
            );
        }

        if ($request->isMethod('POST')) {
            $this->unsubscribeManager->unsubscribe($context->adherent, $context->memberId, $context->messageUuid);

            return $this->render('renaissance/subscription/unsubscribe_done.html.twig');
        }

        return $this->render('renaissance/subscription/unsubscribe_confirm.html.twig');
    }
}
