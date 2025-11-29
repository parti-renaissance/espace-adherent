<?php

declare(strict_types=1);

namespace App\Controller\Api\Phoning;

use App\CmsBlock\CmsBlockManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/phoning_campaigns/tutorial', name: 'api_get_phoning_campaigns_tutorial', methods: ['GET'])]
class GetPhoningCampaignTutorialController extends AbstractController
{
    public function __invoke(CmsBlockManager $manager): JsonResponse
    {
        return $this->json(['content' => $manager->getContent('phoning-campaign-tutorial')]);
    }
}
