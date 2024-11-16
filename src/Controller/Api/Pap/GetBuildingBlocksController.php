<?php

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Building;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')"))]
#[Route(path: '/v3/pap/buildings/{uuid}/building_blocks', name: 'api_get_building_blocks', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class GetBuildingBlocksController extends AbstractController
{
    public function __invoke(Building $building): Response
    {
        return $this->json(
            $building->getBuildingBlocks(),
            Response::HTTP_OK,
            [],
            ['groups' => ['pap_building_block_list']]
        );
    }
}
