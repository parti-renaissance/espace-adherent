<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\OrderArticle;
use AppBundle\Entity\OrderSection;
use AppBundle\Entity\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExplainerController extends AbstractController
{
    /**
     * @Route("/transformer-la-france", name="app_explainer_index")
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
     * @Route("/transformer-la-france/{slug}", name="app_explainer_article_show")
     * @Method("GET")
     * @Entity("explainer", expr="repository.findPublishedArticle(slug)")
     */
    public function proposalAction(OrderArticle $explainer)
    {
        return $this->render('explainer/article.html.twig', ['explainer' => $explainer]);
    }
}
