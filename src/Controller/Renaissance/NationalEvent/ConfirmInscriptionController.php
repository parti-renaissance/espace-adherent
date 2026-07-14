<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Entity\NationalEvent\EventInscription;
use App\Form\ConfirmActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/{uuid}/confirmer', name: 'app_national_event_confirm_inscription', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
class ConfirmInscriptionController extends AbstractController
{
    private PostHogService $postHog;

    #[Required]
    public function setPostHogService(PostHogService $postHog): void
    {
        $this->postHog = $postHog;
    }

    public function __invoke(Request $request, EntityManagerInterface $entityManager, EventInscription $inscription): Response
    {
        $form = $this
            ->createForm(ConfirmActionType::class, null, ['with_deny' => false])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted()) {
            if (!$inscription->confirmedAt) {
                $inscription->confirmedAt = new \DateTime();
                $entityManager->flush();
            }

            $this->postHog->captureServerSide(
                PostHogEventName::NATIONAL_EVENT_INSCRIPTION_CONFIRMED,
                [
                    'inscription_uuid' => $inscription->getUuid()->toRfc4122(),
                    'event_uuid' => $inscription->event->getUuid()->toRfc4122(),
                ],
                $inscription->adherent,
            );

            $this->addFlash('success', 'Merci d\'avoir confirmé votre présence !');

            return $this->redirectToRoute('app_national_event_my_inscription', [
                'slug' => $inscription->event->getSlug(),
                'uuid' => $inscription->getUuid()->toRfc4122(),
            ]);
        }

        return $this->render('renaissance/national_event/confirm_inscription.html.twig', [
            'form' => $form->createView(),
            'event' => $inscription->event,
            'inscription' => $inscription,
        ]);
    }
}
