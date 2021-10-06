<?php

namespace App\Controller\EnMarche\AdherentProfile;

use App\Entity\Adherent;
use App\Entity\AdherentEmailSubscribeToken;
use App\Exception\AdherentTokenException;
use App\Mailchimp\SignUp\SignUpHandler;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/reabonnement-email/{adherent_uuid}/{email_subscribe_token}",
 *     requirements={
 *         "adherent_uuid": "%pattern_uuid%",
 *         "email_subscribe_token": "%pattern_sha1%"
 *     },
 *     name="app_adherent_profile_email_subscribe",
 *     methods={"GET"}
 * )
 *
 * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
 * @Entity("token", expr="repository.findByToken(email_subscribe_token)")
 */
class EmailSubscribeController extends AbstractController
{
    public function __invoke(
        Request $request,
        Adherent $adherent,
        AdherentEmailSubscribeToken $token,
        EntityManagerInterface $entityManager,
        SignUpHandler $signUpHandler
    ): Response {
        if ($request->query->has('consume') && $request->isXmlHttpRequest()) {
            try {
                $token->consume($adherent);
            } catch (AdherentTokenException $e) {
                return $this->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }

            $entityManager->flush();

            return $this->json(['status' => 'success']);
        }

        $error = false;

        try {
            $token->validate($adherent);
        } catch (AdherentTokenException $e) {
            $error = true;
        }

        return $this->render('adherent_profile/email_subscribe.html.twig', [
            'error' => $error,
            'token' => $token,
            'signup_payload' => !$error ? [
                'url' => $signUpHandler->getMailchimpSignUpHost(),
                'payload' => $signUpHandler->generatePayload($adherent),
            ] : null,
        ]);
    }
}
