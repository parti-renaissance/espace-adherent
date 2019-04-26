<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\OrderArticle;
use AppBundle\Entity\OrderSection;
use AppBundle\Entity\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transformer-la-france")
 */
class ExplainerController extends AbstractController
{
    /**
     * @Route(name="app_explainer_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('explainer/index.html.twig', [
            'sections' => $this->getDoctrine()->getRepository(OrderSection::class)->findAllOrderedByPosition(),
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('les-ordonnances-expliquees'),
        ]);
    }

    /**
     * @Route("/{slug}", name="app_explainer_article_show")
     * @Method("GET")
     * @Entity("article", expr="repository.findPublishedArticle(slug)")
     */
    public function proposalAction(OrderArticle $article)
    {
        return $this->render('explainer/article.html.twig', ['article' => $article]);
    }
}
