<?php

namespace App\Controller\Api\Referral;

use App\Entity\Adherent;
use App\Repository\ReferralRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ScoreboardController extends AbstractController
{
    public function __invoke(
        ReferralRepository $referralRepository,
        #[CurrentUser] Adherent $user,
    ): Response {
        $assembly = $user->getAssemblyZone();

        $prepareRowCallback = static function (array $row) use ($user) {
            if ($row['id'] === $user->getId()) {
                $row['is_current_user'] = true;
            }
            unset($row['id']);

            return $row;
        };

        return $this->json([
            'global' => array_map($prepareRowCallback, $referralRepository->getScoreboard()),
            'global_rank' => $referralRepository->getReferrerRank($user->getId()),
            'assembly' => array_map($prepareRowCallback, $referralRepository->getScoreboard($assembly)),
            'assembly_rank' => $referralRepository->getReferrerRank($user->getId(), $assembly),
        ]);
    }
}
