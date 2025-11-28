<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adhesion;

use App\Controller\Renaissance\Payment\StatusController;
use App\Entity\Adherent;
use App\Repository\DonationRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/adhesion/felicitations', name: 'app_adhesion_finish', methods: ['GET'])]
class FinishController extends AbstractController
{
    public function __invoke(Request $request, DonationRepository $donationRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME);
        }

        $type = $user->isRenaissanceAdherent() ? 'adhesion' : 'sympathizer';

        if (
            ($lastDonationUuid = $request->getSession()->get(StatusController::SESSION_KEY))
            && ($donation = $donationRepository->findOneByUuid($lastDonationUuid))
            && $donation->isSuccess()
            && $donation->isMembership()
        ) {
            $type = $donation->isReAdhesion() ? 'readhesion' : 'adhesion';
        }

        $callbackPath = $request->getSession()->remove(AnonymousFollowerSession::SESSION_KEY);

        return $this->render('renaissance/adhesion/finish.html.twig', [
            'type' => $type,
            'callback_path' => $callbackPath,
        ]);
    }
}
