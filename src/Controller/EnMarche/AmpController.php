<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Article;
use AppBundle\Entity\Proposal;
use AppBundle\Entity\OrderArticle;
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
     * @Route("/transformer-la-france/{slug}", defaults={"_enable_campaign_silence"=true}, name="amp_explainer_view")
     * @Method("GET")
     * @Entity("explainer", expr="repository.findOnePublishedBySlug(slug)")
     */
    public function explainerAction(OrderArticle $explainer): Response
    {
        $this->disableProfiler();

        return $this->render('amp/explainer.html.twig', ['explainer' => $explainer]);
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

    private function disableProfiler()
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }
    }
}
