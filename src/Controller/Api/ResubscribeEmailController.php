<?php

namespace App\Controller\Api;

use App\Mailchimp\SignUp\SignUpHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/resubscribe-email", name="api_resubscribe_email_payload", methods={"GET"})
 *
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ResubscribeEmailController extends AbstractController
{
    public function __invoke(UserInterface $adherent, SignUpHandler $signUpHandler): Response
    {
        return $this->json([
            'url' => $signUpHandler->getMailchimpSignUpHost(),
            'payload' => $signUpHandler->generatePayload($adherent),
        ]);
    }
}
