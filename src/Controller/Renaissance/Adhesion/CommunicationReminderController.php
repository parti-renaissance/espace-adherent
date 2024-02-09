<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Request\UpdateCommunicationRequest;
use App\Entity\Adherent;
use App\Form\AdhesionCommunicationType;
use App\Repository\SubscriptionTypeRepository;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/rappel-communication', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class CommunicationReminderController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_communication_reminder';

    public function __construct(
        private readonly SubscriptionTypeRepository $subscriptionTypeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::COMMUNICATION)) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        $acceptSms = $adherent->hasSmsSubscriptionType();
        $acceptEmail = $adherent->hasSubscriptionType(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL);
        $hasPhone = null !== $adherent->getPhone();

        if ($acceptSms && $acceptEmail && $hasPhone) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::COMMUNICATION);
            $this->entityManager->flush();

            return $this->redirectToRoute(CommitteeController::ROUTE_NAME);
        }

        $form = $this
            ->createForm(AdhesionCommunicationType::class, $data = UpdateCommunicationRequest::fromAdherent($adherent))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($data->phone) {
                $adherent->setPhone($data->phone);
            }

            if ($data->acceptEmail) {
                $adherent->addSubscriptionType($this->subscriptionTypeRepository->findOneByCode(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL));
            }

            if ($data->acceptSms) {
                $adherent->addSubscriptionType($this->subscriptionTypeRepository->findOneByCode(SubscriptionTypeEnum::MILITANT_ACTION_SMS));
            }

            $adherent->finishAdhesionStep(AdhesionStepEnum::COMMUNICATION);
            $this->entityManager->flush();

            return $this->redirectToRoute(CommitteeController::ROUTE_NAME);
        }

        return $this->renderForm('renaissance/adhesion/communication_reminder.html.twig', [
            'form' => $form,
            'accept_sms' => $acceptSms,
            'accept_email' => $acceptEmail,
            'has_phone' => $hasPhone,
        ]);
    }
}
