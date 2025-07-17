<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Form\NationalEvent\CampusTransportType;
use App\NationalEvent\DTO\InscriptionRequest;
use App\NationalEvent\EventInscriptionManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/{slug}/{uuid}/changer-de-forfait', name: 'app_national_event_edit_transport', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
class EditTransportController extends AbstractController
{
    public function __construct(private readonly EventInscriptionManager $eventInscriptionManager)
    {
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

        if (!$event->isCampus()) {
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
            ->createForm(CampusTransportType::class, $inscriptionRequest, [
                'transport_configuration' => $event->transportConfiguration,
                'reserved_places' => $this->eventInscriptionManager->countReservedPlaces($event),
                'validation_groups' => ['campus_transport_update'],
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $payment = $this->eventInscriptionManager->updatePackage($inscriptionRequest, $inscription);

            if ($payment) {
                return $this->redirectToRoute('app_national_event_payment', [
                    'slug' => $event->getSlug(),
                    'uuid' => $payment->getUuid()->toString(),
                ]);
            }

            $this->addFlash('success', 'Votre inscription a bien été mise à jour.');

            return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toString(), 'app_domain' => $request->attributes->get('app_domain')]);
        }

        return $this->render('renaissance/national_event/edit_transport.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'inscription' => $inscription,
        ]);
    }
}
