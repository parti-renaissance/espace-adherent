<?php

namespace App\Controller\EnMarche;

use App\Entity\Clarification;
use App\Entity\Page;
use App\Repository\ClarificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DesintoxController extends AbstractController
{
    #[Entity('page', expr: "repository.findOneBySlug('desintox')")]
    #[Route(path: '/desintox', name: 'desintox_list', methods: ['GET'])]
    public function listAction(Page $page, ClarificationRepository $clarificationRepository)
    {
        return $this->render('desintox/list.html.twig', [
            'page' => $page,
            'clarifications' => $clarificationRepository->findAllPublished(),
        ]);
    }

    #[Entity('clarification', expr: 'repository.findPublishedClarification(slug)')]
    #[Route(path: '/desintox/{slug}', name: 'desintox_view', methods: ['GET'])]
    public function viewAction(Clarification $clarification)
    {
        return $this->render('desintox/view.html.twig', ['clarification' => $clarification]);
    }
}
