<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Article;
use AppBundle\Entity\OrderArticle;
use AppBundle\Entity\Proposal;
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
        $this->disableProfiler();

        return $this->render('amp/article.html.twig', ['article' => $article]);
    }

    /**
     * @Route("/proposition/{slug}", defaults={"_enable_campaign_silence"=true}, name="amp_proposal_view")
     * @Method("GET")
     * @Entity("proposal", expr="repository.findPublishedProposal(slug)")
     */
    public function proposalAction(Proposal $proposal): Response
    {
        $this->disableInProduction();
        $this->disableProfiler();

        return $this->render('amp/proposal.html.twig', ['proposal' => $proposal]);
    }

    /**
     * @Route("/transformer-la-france/{slug}", name="amp_explainer_article_show")
     * @Method("GET")
     * @Entity("article", expr="repository.findPublishedArticle(slug)")
     */
    public function orderArticleAction(OrderArticle $article): Response
    {
        $this->disableProfiler();

        return $this->render('amp/order_article.html.twig', ['article' => $article]);
    }

    private function disableProfiler()
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }
    }
}
