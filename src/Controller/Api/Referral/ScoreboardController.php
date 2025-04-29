<?php

namespace App\Controller\Api\Referral;

use App\Entity\Adherent;
use App\Repository\ReferralRepository;
use App\Scope\GeneralScopeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ScoreboardController extends AbstractController
{
    public function __invoke(
        Request $request,
        AuthorizationCheckerInterface $authorizationChecker,
        GeneralScopeGenerator $generalScopeGenerator,
        ReferralRepository $referralRepository,
        #[CurrentUser] Adherent $user,
    ): Response {
        if (
            $request->query->has('scope')
            && $authorizationChecker->isGranted('REQUEST_SCOPE_GRANTED', 'referrals')
        ) {
            $scopeGenerator = $generalScopeGenerator->getGenerator($request->query->get('scope'), $user);

            $zones = $scopeGenerator->generate($user)->getZones();

            $scoreboards = [];
            foreach ($zones as $zone) {
                $scoreboards[$zone->getCode()] = $referralRepository->getManagerScoreboard($zone);
            }

            return $this->json($scoreboards);
        }

        $assembly = $user->getAssemblyZone();

        return $this->json([
            'global' => $referralRepository->getScoreboard(),
            'global_rank' => $referralRepository->getReferrerRank($user->getId()),
            'assembly' => $referralRepository->getScoreboard($assembly),
            'assembly_rank' => $referralRepository->getReferrerRank($user->getId(), $assembly),
        ]);
    }
}
