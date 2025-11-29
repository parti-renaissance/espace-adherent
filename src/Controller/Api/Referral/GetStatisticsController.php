<?php

declare(strict_types=1);

namespace App\Controller\Api\Referral;

use App\Entity\Adherent;
use App\Repository\ReferralRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('RENAISSANCE_ADHERENT')]
#[Route(path: '/v3/referrals/statistics', name: 'api_get_referral_statistics', methods: ['GET'])]
class GetStatisticsController extends AbstractController
{
    public function __invoke(ReferralRepository $referralRepository): Response
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        return $this->json($referralRepository->getStatistics($user));
    }
}
