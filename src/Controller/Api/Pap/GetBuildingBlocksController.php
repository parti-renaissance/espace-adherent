<?php

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Building;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/pap/buildings/{uuid}/building_blocks', name: 'api_get_building_blocks', methods: ['GET'], requirements: ['uuid' => '%pattern_uuid%'])]
#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')")]
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
