<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\OrderArticle;
use AppBundle\Entity\OrderSection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExplainerController extends AbstractController
{
    /**
     * @Route("/ordonnances", name="app_explainer_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('explainer/index.html.twig', [
            'sections' => $this->getDoctrine()->getRepository(OrderSection::class)->findAllOrderedByPosition(),
        ]);
    }

    /**
     * @Route("/ordonnances/{slug}", name="app_explainer_article_show")
     * @Method("GET")
     * @Entity("article", expr="repository.findPublishedArticle(slug)")
     */
    public function proposalAction(OrderArticle $article)
    {
        return $this->render('explainer/article.html.twig', ['article' => $article]);
    }
}
