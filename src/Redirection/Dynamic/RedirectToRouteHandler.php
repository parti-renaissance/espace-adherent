<?php

namespace App\Redirection\Dynamic;

use App\Repository\ArticleRepository;
use App\Repository\EventRepository;
use App\Repository\OrderArticleRepository;
use App\Repository\ProposalRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectToRouteHandler extends AbstractRedirectTo implements RedirectToInterface
{
    private $provider;
    private $urlGenerator;
    private $eventRepository;
    private $articleRepository;
    private $proposalRepository;
    private $orderArticleRepository;

    public function __construct(
        RedirectionsProvider $provider,
        UrlGeneratorInterface $urlGenerator,
        EventRepository $eventRepository,
        ArticleRepository $articleRepository,
        ProposalRepository $proposalRepository,
        OrderArticleRepository $orderArticleRepository
    ) {
        $this->provider = $provider;
        $this->urlGenerator = $urlGenerator;
        $this->eventRepository = $eventRepository;
        $this->articleRepository = $articleRepository;
        $this->proposalRepository = $proposalRepository;
        $this->orderArticleRepository = $orderArticleRepository;
    }

    public function handle(GetResponseForExceptionEvent $event, string $requestUri, string $redirectCode): bool
    {
        foreach ($this->provider->get(RedirectionsProvider::TO_ROUTE) as $pattern => $route) {
            if (!$this->hasPattern($pattern, $requestUri)) {
                continue;
            }

            $urlToRedirect = null;

            if ($this->hasPattern('/article/', $requestUri)) {
                $articleSlug = substr($requestUri, 9);

                if (!$article = $this->articleRepository->findOnePublishedBySlug($articleSlug)) {
                    continue;
                }

                $urlToRedirect = $this->urlGenerator->generate($route, [
                    'categorySlug' => $article->getCategory()->getSlug(),
                    'articleSlug' => $article->getSlug(),
                ]);
            }

            if ($this->hasPattern('/amp/article/', $requestUri)) {
                $articleSlug = substr($requestUri, 13);

                if (!$article = $this->articleRepository->findOnePublishedBySlug($articleSlug)) {
                    continue;
                }

                $urlToRedirect = $this->urlGenerator->generate($route, [
                    'categorySlug' => $article->getCategory()->getSlug(),
                    'articleSlug' => $article->getSlug(),
                ]);
            }

            if ($this->hasPattern('/amp/proposition/', $requestUri)) {
                $proposalSlug = substr($requestUri, 17);

                if (!$proposal = $this->proposalRepository->findPublishedProposal($proposalSlug)) {
                    continue;
                }

                $urlToRedirect = $this->urlGenerator->generate($route, [
                    'slug' => $proposal->getSlug(),
                ]);
            }

            if ($this->hasPattern('/amp/transformer-la-france/', $requestUri)) {
                $orderArticleSlug = substr($requestUri, 27);

                if (!$orderArticle = $this->orderArticleRepository->findPublishedArticle($orderArticleSlug)) {
                    continue;
                }

                $urlToRedirect = $this->urlGenerator->generate($route, [
                    'slug' => $orderArticle->getSlug(),
                ]);
            }

            $event->setResponse(new RedirectResponse($urlToRedirect, $redirectCode));

            return true;
        }

        return false;
    }
}
