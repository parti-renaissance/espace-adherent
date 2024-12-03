<?php

namespace App\Controller\Api;

use App\Entity\Mooc\Mooc;
use App\Repository\MoocRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mooc')]
class MoocController extends AbstractController
{
    #[Route(path: '', name: 'api_mooc_landing', methods: ['GET'])]
    public function moocLandingPageAction(MoocRepository $moocRepository): Response
    {
        return $this->json($moocRepository->findAllOrdered(), Response::HTTP_OK, [], ['groups' => ['mooc_list']]);
    }

    #[Entity('mooc', expr: 'repository.findOneBySlug(slug)')]
    #[Route(path: '/{slug}', name: 'api_mooc', methods: ['GET'])]
    public function moocAction(Mooc $mooc): Response
    {
        return $this->json($mooc, Response::HTTP_OK, [], ['groups' => ['mooc_read']]);
    }
}
