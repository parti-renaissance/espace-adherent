<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adhesion;

use App\Address\PostAddressFactory;
use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Request\MembershipRequest;
use App\Entity\Adherent;
use App\Form\MemberCardType;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Referent\ReferentZoneManager;
use App\Utils\UtmParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/adhesion/carte-adherent', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class MemberCardController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_member_card';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ReferentZoneManager $referentZoneManager,
    ) {
    }

    public function __invoke(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $utmParams = UtmParams::fromRequest($request);

        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME, $utmParams);
        }

        if (!$adherent->isRenaissanceAdherent()) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::MEMBER_CARD);
            $this->entityManager->flush();

            return $this->redirectToRoute(CommunicationReminderController::ROUTE_NAME, $utmParams);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::MEMBER_CARD)) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        $form = $this
            ->createForm(MemberCardType::class, $membershipRequest = MembershipRequest::createFromAdherent($adherent))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted()) {
            if ($form->get('refuseMemberCard')->isClicked() || $form->isValid()) {
                $adherent->finishAdhesionStep(AdhesionStepEnum::MEMBER_CARD);

                if ($form->get('refuseMemberCard')->isClicked()) {
                    $adherent->acceptMemberCard = false;
                    $this->entityManager->flush();
                } else {
                    $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);

                    $adherent->setPostAddress(PostAddressFactory::createFromAddress($membershipRequest->address));

                    if ($this->referentZoneManager->isUpdateNeeded($adherent)) {
                        $this->referentZoneManager->assignZone($adherent);
                    }

                    $this->entityManager->flush();

                    $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATED);
                }

                return $this->redirectToRoute(CommunicationReminderController::ROUTE_NAME, $utmParams);
            }
        }

        return $this->render('renaissance/adhesion/member_card.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
