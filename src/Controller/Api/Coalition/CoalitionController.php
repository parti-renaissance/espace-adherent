<?php

namespace App\Controller\Api\Coalition;

use App\Entity\Coalition\Coalition;
use App\Repository\Coalition\CoalitionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CoalitionController extends AbstractController
{
    /**
     * @Route("/v3/coalitions/followed", name="api_coalitions_followed", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function followed(UserInterface $user, CoalitionRepository $coalitionRepository): JsonResponse
    {
        $coalitions = $coalitionRepository->findFollowedBy($user);

        return JsonResponse::create(array_map(function (Coalition $coalition) {
            return $coalition->getUuid();
        }, $coalitions));
    }
}
