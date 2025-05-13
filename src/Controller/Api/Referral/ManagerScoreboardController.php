<?php

namespace App\Controller\Api\Referral;

use App\Repository\ReferralRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManagerScoreboardController extends AbstractController
{
    public function __invoke(ScopeGeneratorResolver $generatorResolver, ReferralRepository $referralRepository): Response
    {
        if (!$scope = $generatorResolver->generate()) {
            throw $this->createAccessDeniedException();
        }

        return $this->json(array_map(function (array $row) {
            if (!empty($row['profile_image'])) {
                $row['profile_image'] = $this->generateUrl('asset_url', ['path' => \sprintf('images/profile/%s', $row['profile_image'])], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            return $row;
        }, $referralRepository->getManagerScoreboard($scope->getZones())));
    }
}
