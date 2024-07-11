<?php

namespace App\Controller\EnMarche;

use App\Entity\OrderArticle;
use App\Repository\OrderSectionRepository;
use App\Repository\PageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/transformer-la-france')]
class ExplainerController extends AbstractController
{
    #[Route(name: 'app_explainer_index', methods: ['GET'])]
    public function indexAction(
        OrderSectionRepository $orderSectionRepository,
        PageRepository $pageRepository
    ): Response {
        return $this->render('explainer/index.html.twig', [
            'sections' => $orderSectionRepository->findAllOrderedByPosition(),
            'page' => $pageRepository->findOneBySlug('les-ordonnances-expliquees'),
        ]);
    }

    #[Entity('article', expr: 'repository.findPublishedArticle(slug)')]
    #[Route(path: '/{slug}', name: 'app_explainer_article_show', methods: ['GET'])]
    public function proposalAction(OrderArticle $article): Response
    {
        return $this->render('explainer/article.html.twig', ['article' => $article]);
    }
}
