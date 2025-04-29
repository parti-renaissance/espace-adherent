<?php

namespace App\Controller\Api\Referral;

use App\Entity\Adherent;
use App\Repository\ReferralRepository;
use App\Scope\GeneralScopeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ManagerScoreboardController extends AbstractController
{
    public function __invoke(
        Request $request,
        GeneralScopeGenerator $generalScopeGenerator,
        ReferralRepository $referralRepository,
        #[CurrentUser] Adherent $user,
    ): Response {
        $scopeGenerator = $generalScopeGenerator->getGenerator($request->query->get('scope'), $user);

        $zones = $scopeGenerator->generate($user)->getZones();

        $scoreboards = [];
        foreach ($zones as $zone) {
            $scoreboards[$zone->getCode()] = $referralRepository->getManagerScoreboard($zone);
        }

        return $this->json($scoreboards);
    }
}
