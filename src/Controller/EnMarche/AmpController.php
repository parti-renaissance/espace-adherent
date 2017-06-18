<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Article;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/amp")
 */
class AmpController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/article/{slug}", defaults={"_enable_campaign_silence"=true}, name="amp_article_view")
     * @Method("GET")
     * @Entity("article", expr="repository.findOnePublishedBySlug(slug)")
     */
    public function articleAction(Article $article): Response
    {
        $this->disableInProduction();

        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        return $this->render('amp/article.html.twig', ['article' => $article]);
    }
}
