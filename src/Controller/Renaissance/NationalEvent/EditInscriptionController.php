<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Form\NationalEvent\UserDataFormType;
use App\NationalEvent\DTO\InscriptionRequest;
use App\NationalEvent\EventInscriptionManager;
use App\NationalEvent\InscriptionStatusEnum;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/{slug}/{uuid}/modifier-mes-informations', name: 'app_national_event_edit_inscription', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
class EditInscriptionController extends AbstractController
{
    private PostHogService $postHog;

    #[Required]
    public function setPostHogService(PostHogService $postHog): void
    {
        $this->postHog = $postHog;
    }

    public function __construct(
        private readonly EventInscriptionManager $eventInscriptionManager,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        #[CurrentUser] ?Adherent $user = null,
    ): Response {
        if ($inscription->event !== $event) {
            throw $this->createNotFoundException('Inscription not found for this event.');
        }

        if (!$inscription->allowEditInscription()) {
            $this->addFlash('error', 'L\'édition de votre inscription n\'est plus autorisée.');

            if ($event->isPackageEventType()) {
                return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toRfc4122(), 'app_domain' => $request->attributes->get('app_domain')]);
            }

            return $this->redirectToRoute('app_national_event_by_slug', ['slug' => $event->getSlug(), 'app_domain' => $request->attributes->get('app_domain')]);
        }

        $inscriptionRequest = InscriptionRequest::fromInscription($inscription);

        $form = $this
            ->createForm(UserDataFormType::class, $inscriptionRequest, [
                'is_edit' => true,
                'adherent' => $user,
                'event' => $event,
                'validation_groups' => ['Default', 'inscription:user_data', 'inscription:user_data:'.$event->type->value],
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->status = InscriptionStatusEnum::PENDING;

            $this->eventInscriptionManager->saveInscription($event, $inscriptionRequest, $inscription);

            // Cas 1 forcé (spec §8.4) — pas de $set.email PostHog.
            $this->postHog->captureServerSide(
                PostHogEventName::NATIONAL_EVENT_INSCRIPTION_EDITED,
                [
                    'event_uuid' => $event->getUuid()->toRfc4122(),
                    'inscription_uuid' => $inscription->getUuid()->toRfc4122(),
                ],
                $user,
            );

            $this->addFlash('success', 'Votre inscription a bien été mise à jour.');

            if ($event->isPackageEventType()) {
                return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toRfc4122(), 'app_domain' => $request->attributes->get('app_domain')]);
            }

            return $this->redirectToRoute('app_national_event_edit_inscription', ['uuid' => $inscription->getUuid(), 'slug' => $event->getSlug(), 'app_domain' => $request->attributes->get('app_domain')]);
        }

        return $this->render('renaissance/national_event/edit_inscription.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'inscription' => $inscription,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'is_open' => true,
            'is_edit' => true,
        ]);
    }
}
