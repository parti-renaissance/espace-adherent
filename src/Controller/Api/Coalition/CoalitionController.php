<?php

namespace App\Controller\Api\Coalition;

use App\Entity\Adherent;
use App\Entity\Coalition\Coalition;
use App\Repository\Coalition\CoalitionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CoalitionController extends AbstractController
{
    /**
     * @Route("/v3/coalitions/followed", name="api_coalitions_followed", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function followed(CoalitionRepository $coalitionRepository): JsonResponse
    {
        /** @var Adherent $user */
        $user = $this->getUser();
        $coalitions = $coalitionRepository->findFollowedBy($user);

        return JsonResponse::create(array_map(function (Coalition $coalition) {
            return $coalition->getUuid();
        }, $coalitions));
    }
}
