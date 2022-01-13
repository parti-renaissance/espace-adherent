<?php

namespace App\Controller\Api\JeMengage;

use App\CmsBlock\CmsBlockManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/je-mengage/rgpd", name="api_get_je_mengage_rgpd", methods={"GET"})
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') or is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')")
 */
class GetRgpdController extends AbstractController
{
    public function __invoke(CmsBlockManager $manager): JsonResponse
    {
        return $this->json(['content' => $manager->getContent('je-mengage-rgpd')]);
    }
}
