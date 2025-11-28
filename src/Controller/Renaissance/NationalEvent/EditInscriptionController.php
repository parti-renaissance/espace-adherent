<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Form\NationalEvent\CampusEventInscriptionType;
use App\Form\NationalEvent\DefaultEventInscriptionType;
use App\NationalEvent\DTO\InscriptionRequest;
use App\NationalEvent\EventInscriptionManager;
use App\NationalEvent\InscriptionStatusEnum;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/{slug}/{uuid}/modifier-mes-informations', name: 'app_national_event_edit_inscription', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
class EditInscriptionController extends AbstractController
{
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

            if ($event->isCampus()) {
                return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toString(), 'app_domain' => $request->attributes->get('app_domain')]);
            }

            return $this->redirectToRoute('app_national_event_by_slug', ['slug' => $event->getSlug(), 'app_domain' => $request->attributes->get('app_domain')]);
        }

        $inscriptionRequest = InscriptionRequest::fromInscription($inscription);

        $form = $this
            ->createInscriptionForm($event, $inscriptionRequest, $user)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->status = InscriptionStatusEnum::PENDING;

            $this->eventInscriptionManager->saveInscription($event, $inscriptionRequest, $inscription);

            $this->addFlash('success', 'Votre inscription a bien été mise à jour.');

            if ($event->isCampus()) {
                return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toString(), 'app_domain' => $request->attributes->get('app_domain')]);
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

    protected function createInscriptionForm(NationalEvent $event, InscriptionRequest $eventInscriptionRequest, ?Adherent $adherent): FormInterface
    {
        $defaultOptions = [
            'is_edit' => true,
            'adherent' => $adherent,
        ];

        if ($event->isCampus()) {
            return $this->createForm(CampusEventInscriptionType::class, $eventInscriptionRequest, array_merge($defaultOptions, [
                'validation_groups' => ['Default', 'inscription_campus_edit'],
            ]));
        }

        return $this->createForm(DefaultEventInscriptionType::class, $eventInscriptionRequest, $defaultOptions);
    }
}
