<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Entity\Adherent;
use App\Mailchimp\SignUp\SignUpHandler;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailchimpUpdateCleanedContactController extends AbstractController
{
    #[Route('/mailchimp/update-cleaned/{key}', name: 'api_mailchimp_update_cleaned_contact', methods: ['GET'])]
    public function runWidgetAction(string $key, string $updateCleanedContactToken, string $updateCleanedContactApiToken, AdherentRepository $repository, EntityManagerInterface $entityManager, SignUpHandler $signUpHandler): Response
    {
        if ($key !== $updateCleanedContactToken) {
            throw $this->createAccessDeniedException('Token invalide');
        }

        if (!$adherent = $repository->findNextCleaned()) {
            throw $this->createNotFoundException();
        }

        $adherent->resubscribeEmailStartedAt = new \DateTimeImmutable();
        $entityManager->flush();

        return $this->render('renaissance/update_cleaned_contact.html.twig', [
            'api_key' => $updateCleanedContactApiToken,
            'uuid' => $adherent->getUuid(),
            'signup_payload' => [
                'url' => $signUpHandler->getMailchimpSignUpHost(),
                'payload' => $signUpHandler->generatePayload($adherent),
            ],
        ]);
    }

    #[Route('/mailchimp/update-cleaned/{uuid}/save-last-response', name: 'api_mailchimp_update_cleaned_contact_save_last_response', methods: ['PUT'])]
    public function saveLastResponseAction(Request $request, EntityManagerInterface $entityManager, Adherent $adherent, string $updateCleanedContactApiToken): Response
    {
        if ($request->headers->get('x-api-key') === $updateCleanedContactApiToken) {
            $data = $request->toArray();

            if (!empty($data['data'])) {
                $adherent->resubscribeResponse = json_encode($data['data']);
                $entityManager->flush();
            }
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
