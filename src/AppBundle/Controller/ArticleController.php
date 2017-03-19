<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArticleController extends Controller
{
    const PER_PAGE = 12;

    /**
     * @Route(
     *     "/feed",
     *     name="articles_feed"
     * )
     * @Method("GET")
     */
    public function feedAction()
    {
        $articlesRepo = $this->getDoctrine()->getRepository(Article::class);

        $articles = $articlesRepo->findAllForFeed();

        if (empty($articles)) {
            throw $this->createNotFoundException();
        }

        $mostRecentArticleDateTimestamp = $articles[0]->getPublishedAt()->format('U');

        $feed = new Feed();
        $channel = new Channel();
        $channel->title('En Marche!')
            ->url($this->get('router')->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL))
            ->language($this->getParameter('locale'))
            ->copyright(sprintf('Copyright %d, En Marche!', date('Y')))
            ->pubDate($mostRecentArticleDateTimestamp)
            ->lastBuildDate($mostRecentArticleDateTimestamp)
            ->ttl(120) // Move to a parameter?
            ->appendTo($feed);

        $urlGenerator = $this->get('router');
        $markDownParser = $this->get('app.content.markdown_parser');

        foreach ($articles as $article) {
            $articleUrl = $urlGenerator->generate('article_view', ['slug' => $article->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            $item = new Item();
            $item->title($article->getTitle())
                ->url($articleUrl)
                ->description($markDownParser->convertToHtml($article->getContent()))
                ->category($article->getCategory()->getName())
                ->guid($articleUrl, true)
                ->preferCdata(true)
                ->appendTo($channel);
        }

        return new Response(
            $feed->render(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/rss+xml; charset=UTF-8',
            ]
        );
    }

    /**
     * @Route(
     *     "/articles/{category}/{page}",
     *     requirements={"page"="\d+"},
     *     defaults={"page"=1},
     *     name="articles_list"
     * )
     * @Method("GET")
     */
    public function actualitesAction($category, $page)
    {
        $categoriesRepo = $this->getDoctrine()->getRepository(ArticleCategory::class);
        $articlesRepo = $this->getDoctrine()->getRepository(Article::class);

        $category = $categoriesRepo->findOneBySlug($category);

        if (!$category) {
            throw $this->createNotFoundException();
        }

        $page = (int) $page;

        $categories = $categoriesRepo->findAll();
        $articlesCount = $articlesRepo->countAllByCategory($category);
        $articles = $articlesRepo->findByCategoryPaginated($category, $page, self::PER_PAGE);

        if (empty($articles)) {
            throw $this->createNotFoundException();
        }

        return $this->render('article/actualites.html.twig', [
            'current' => $category,
            'categories' => $categories,
            'articles' => $articles,
            'currentPage' => $page,
            'totalPages' => ceil($articlesCount / self::PER_PAGE),
        ]);
    }

    /**
     * @Route("/article/{slug}", name="article_view")
     * @Method("GET")
     */
    public function articleAction($slug)
    {
        $article = $this->getDoctrine()->getRepository('AppBundle:Article')->findOneBySlug($slug);

        if (!$article || !$article->isPublished()) {
            throw $this->createNotFoundException();
        }

        return $this->render('article/article.html.twig', [
            'article' => $article,
        ]);
    }
}
