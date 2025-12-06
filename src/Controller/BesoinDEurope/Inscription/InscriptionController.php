<?php

declare(strict_types=1);

namespace App\Controller\BesoinDEurope\Inscription;

use App\BesoinDEurope\Inscription\FinishInscriptionRedirectHandler;
use App\BesoinDEurope\Inscription\InscriptionRequest;
use App\Controller\BesoinDEurope\Inscription\Api\PersistEmailController;
use App\Form\BesoinDEurope\InscriptionRequestType;
use App\Membership\AdherentFactory;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Utils\UtmParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route('/inscription', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class InscriptionController extends AbstractController
{
    public const ROUTE_NAME = 'app_bde_inscription';

    private int $step = 0;

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly AdherentFactory $adherentFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if ($redirectUri = $request->query->get('redirect_uri')) {
            $request->getSession()->set(FinishInscriptionRedirectHandler::SESSION_KEY, $redirectUri);
        }

        $inscriptionRequest = $this->getInscriptionRequest($request);

        $form = $this
            ->createForm(InscriptionRequestType::class, $inscriptionRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent = $this->adherentFactory->createFromBesoinDEuropeMembershipRequest($inscriptionRequest);
            $this->entityManager->persist($adherent);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new UserEvent($adherent, $inscriptionRequest->allowNotifications), UserEvents::USER_CREATED);

            $this->security->login($adherent);

            $this->addFlash('success', 'Votre compte vient d’être créé');

            return $this->redirectToRoute(ActivateEmailController::ROUTE_NAME);
        }

        return $this->render('besoindeurope/inscription/form.html.twig', [
            'form' => $form->createView(),
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'step' => $this->step,
        ]);
    }

    private function getInscriptionRequest(Request $request): InscriptionRequest
    {
        $inscriptionRequest = new InscriptionRequest();

        if ($emailIdentifier = $request->getSession()->get(PersistEmailController::SESSION_KEY)) {
            $inscriptionRequest->email = $emailIdentifier;
            $this->step = 1;
        } else {
            $inscriptionRequest->email = $request->query->get('email');
        }

        if ($request->query->has(UtmParams::UTM_SOURCE)) {
            $inscriptionRequest->utmSource = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_SOURCE));
        }
        if ($request->query->has(UtmParams::UTM_CAMPAIGN)) {
            $inscriptionRequest->utmCampaign = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_CAMPAIGN));
        }

        return $inscriptionRequest;
    }
}
