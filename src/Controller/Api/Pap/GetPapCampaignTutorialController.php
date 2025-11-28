<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use App\CmsBlock\CmsBlockManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')"))]
#[Route(path: '/v3/pap_campaigns/tutorial', name: 'api_get_pap_campaigns_tutorial', methods: ['GET'])]
class GetPapCampaignTutorialController extends AbstractController
{
    public function __invoke(CmsBlockManager $manager): JsonResponse
    {
        return $this->json(['content' => $manager->getContent('pap-campaign-tutorial')]);
    }
}
