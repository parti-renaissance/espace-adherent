<?php

namespace AppBundle\Controller\Amp;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Article;
use AppBundle\Entity\OrderArticle;
use AppBundle\Entity\Proposal;
use AppBundle\Sitemap\SitemapFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AmpController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/articles/{categorySlug}/{articleSlug}", name="amp_article_view", methods={"GET"})
     * @Entity("article", expr="repository.findOnePublishedBySlugAndCategorySlug(articleSlug, categorySlug)")
     */
    public function articleAction(Article $article): Response
    {
        return $this->render('amp/article.html.twig', ['article' => $article]);
    }

    /**
     * @Route("/proposition/{slug}", name="amp_proposal_view", methods={"GET"})
     * @Entity("proposal", expr="repository.findPublishedProposal(slug)")
     */
    public function proposalAction(Proposal $proposal): Response
    {
        $this->disableInProduction();

        return $this->render('amp/proposal.html.twig', ['proposal' => $proposal]);
    }

    /**
     * @Route("/transformer-la-france/{slug}", name="amp_explainer_article_show", methods={"GET"})
     * @Entity("article", expr="repository.findPublishedArticle(slug)")
     */
    public function orderArticleAction(OrderArticle $article): Response
    {
        return $this->render('amp/order_article.html.twig', ['article' => $article]);
    }

    /**
     * @Route("/sitemap.xml", name="amp_sitemap", methods={"GET"})
     */
    public function sitemapIndexAction(): Response
    {
        return new Response(
            $this->get(SitemapFactory::class)->createAmpSitemap(),
            Response::HTTP_OK,
            ['Content-type' => 'text/xml']
        );
    }
}
