<?php

declare(strict_types=1);

namespace App\Controller\Api\Mailchimp;

use App\Entity\Adherent;
use App\Mailchimp\SignUp\SignUpHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route(path: '/resubscribe-email', name: 'api_resubscribe_email_payload', methods: ['GET'])]
#[Route(path: '/v3/resubscribe-config', methods: ['GET'])]
class ResubscribeEmailController extends AbstractController
{
    public function __invoke(SignUpHandler $signUpHandler): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $this->json([
            'url' => $signUpHandler->getMailchimpSignUpHost(),
            'payload' => $signUpHandler->generatePayload($adherent),
        ]);
    }
}
