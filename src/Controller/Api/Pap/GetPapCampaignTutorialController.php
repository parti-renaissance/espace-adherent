<?php

namespace App\Controller\Api\Pap;

use App\CmsBlock\CmsBlockManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/pap_campaigns/tutorial", name="api_get_pap_campaigns_tutorial", methods={"GET"})
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('IS_PAP_USER')")
 */
class GetPapCampaignTutorialController extends AbstractController
{
    public function __invoke(CmsBlockManager $manager): JsonResponse
    {
        return $this->json(['content' => $manager->getContent('pap-campaign-tutorial')]);
    }
}
