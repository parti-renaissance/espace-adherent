<?php

namespace App\Security;

use App\Adhesion\AdhesionStepEnum;
use App\Controller\Renaissance\MagicLinkController;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class MagicLinkAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $routeName = $request->attributes->get('_route');
        $adherent = $token->getUser();

        if (MagicLinkController::ROUTE_NAME !== $routeName || !$request->isMethod('POST')) {
            return;
        }

        if (!$adherent instanceof Adherent || $adherent->getActivatedAt()) {
            return;
        }

        $adherent->enable();
        $adherent->finishAdhesionStep(AdhesionStepEnum::ACTIVATION);

        $this->entityManager->flush();
    }
}
