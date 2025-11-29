<?php

declare(strict_types=1);

namespace App\Controller\BesoinDEurope\Inscription;

use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Request\UpdateCommunicationRequest;
use App\Entity\Adherent;
use App\Form\AdhesionCommunicationType;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(path: '/inscription/rappel-communication', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class CommunicationReminderController extends AbstractController
{
    public const ROUTE_NAME = 'app_bde_communication_reminder';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(InscriptionController::ROUTE_NAME);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::COMMUNICATION)) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        $acceptSms = $adherent->hasSmsSubscriptionType();
        $acceptEmail = $adherent->hasSubscriptionType(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL);
        $hasPhone = null !== $adherent->getPhone();

        if ($acceptSms && $acceptEmail && $hasPhone) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::COMMUNICATION);
            $this->entityManager->flush();

            return $this->redirectToRoute(FinishController::ROUTE_NAME);
        }

        $form = $this
            ->createForm(AdhesionCommunicationType::class, $data = UpdateCommunicationRequest::fromAdherent($adherent))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($data->phone) {
                $adherent->setPhone($data->phone);
            }

            $adherent->finishAdhesionStep(AdhesionStepEnum::COMMUNICATION);
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new UserEvent($adherent, $data->acceptEmail, $data->acceptSms), UserEvents::USER_UPDATED);

            return $this->redirectToRoute(FinishController::ROUTE_NAME);
        }

        return $this->renderForm('besoindeurope/inscription/communication_reminder.html.twig', [
            'form' => $form,
            'accept_sms' => $acceptSms,
            'accept_email' => $acceptEmail,
            'has_phone' => $hasPhone,
        ]);
    }
}
