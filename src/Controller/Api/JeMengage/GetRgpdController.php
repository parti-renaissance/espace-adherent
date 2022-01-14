<?php

namespace App\Controller\Api\JeMengage;

use App\CmsBlock\CmsBlockManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/je-mengage/rgpd", name="api_get_je_mengage_rgpd", methods={"GET"})
 */
class GetRgpdController extends AbstractController
{
    public function __invoke(CmsBlockManager $manager): JsonResponse
    {
        return $this->json(['content' => $manager->getContent('je-mengage-rgpd')]);
    }
}
