<?php

namespace App\Controller\Api\Vox;

use App\CmsBlock\CmsBlockManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/je-mengage/{slug}', name: 'api_get_je_mengage_block_content', methods: ['GET'])]
class GetJeMengageContentBlockController extends AbstractController
{
    public function __invoke(CmsBlockManager $manager, string $slug): JsonResponse
    {
        if (!$content = $manager->getContent($slug)) {
            throw $this->createNotFoundException();
        }

        return $this->json(['content' => $content]);
    }
}
