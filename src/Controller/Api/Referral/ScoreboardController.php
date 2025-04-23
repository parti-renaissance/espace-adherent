<?php

namespace App\Controller\Api\Referral;

use App\Entity\Adherent;
use App\Repository\ReferralRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('RENAISSANCE_ADHERENT')]
#[Route(path: '/v3/referrals/scoreboard', name: 'api_get_referral_scoreboard', methods: ['GET'])]
class ScoreboardController extends AbstractController
{
    public function __invoke(ReferralRepository $referralRepository): Response
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        $assembly = $user->getAssemblyZone();

        return $this->json([
            'global' => $referralRepository->getScoreboard(),
            'global_rank' => $referralRepository->getReferrerRank($user),
            'assembly' => $referralRepository->getScoreboard($assembly),
            'assembly_rank' => $referralRepository->getReferrerRank($user, $assembly),
        ]);
    }
}
