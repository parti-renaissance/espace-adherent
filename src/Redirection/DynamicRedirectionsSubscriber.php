<?php

namespace AppBundle\Redirection;

use AppBundle\Repository\ArticleRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\OrderArticleRepository;
use AppBundle\Repository\ProposalRepository;
use AppBundle\Repository\RedirectionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Handle dynamic redirections editable in the administration panel.
 */
class DynamicRedirectionsSubscriber implements EventSubscriberInterface
{
    const REDIRECTIONS = [
        '/evenements/' => '/evenements',
        '/initiative-citoyenne/' => '/initiative-citoyenne',
        '/comites/' => '/comites',
        '/articles/tout' => '/articles',
        '/article/' => '/articles/',
        // AMP pages are redirected to another subdomain
        '/amp/article/' => null,
        '/amp/proposition/' => null,
        '/amp/transformer-la-france/' => null,
    ];

    private $redirectRepository;
    private $router;
    private $eventRepository;
    private $articleRepository;
    private $proposalRepository;
    private $orderArticleRepository;
    private $patternUuid;

    public function __construct(
        RedirectionRepository $redirectionRepository,
        EventRepository $eventRepository,
        ArticleRepository $articleRepository,
        ProposalRepository $proposalRepository,
        OrderArticleRepository $orderArticleRepository,
        RouterInterface $router,
        string $patternUuid
    ) {
        $this->redirectRepository = $redirectionRepository;
        $this->router = $router;
        $this->eventRepository = $eventRepository;
        $this->articleRepository = $articleRepository;
        $this->proposalRepository = $proposalRepository;
        $this->orderArticleRepository = $orderArticleRepository;
        $this->patternUuid = $patternUuid;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        $requestUri = rtrim($event->getRequest()->getRequestUri(), '/');

        if ($redirection = $this->redirectRepository->findOneByOriginUri($requestUri)) {
            $event->setResponse(new RedirectResponse($redirection->getTo(), $redirection->getType()));

            return;
        }

        $redirectCode = Response::HTTP_MOVED_PERMANENTLY;
        foreach (self::REDIRECTIONS as $patternToMatch => $urlToRedirect) {
            if (0 !== strpos($requestUri, $patternToMatch)) {
                continue;
            }

            // For all old URLs with uuid/slug we remove uuid from URL
            preg_match(sprintf("/\/%s/", $this->patternUuid), $requestUri, $matches);
            if (count($matches) > 0) {
                $redirectionUrl = str_replace($matches[0], '', $requestUri);
                $event->setResponse(new RedirectResponse($redirectionUrl, $redirectCode));

                return;
            }

            try {
                $routeParams = $this->router->match($requestUri);
            } catch (ResourceNotFoundException | MethodNotAllowedException $e) {
                $routeParams = false;
            }

            if ('/evenements/' === $patternToMatch
                && $routeParams
                && isset($routeParams['uuid'])
                && ($eventEntity = $this->eventRepository->findOneByUuid($routeParams['uuid']))
                && !$eventEntity->isPublished()) {
                $redirectCode = Response::HTTP_FOUND;
            }

            if (0 === strpos($requestUri, '/article/')) {
                $articleSlug = substr($requestUri, 9);

                if (!$article = $this->articleRepository->findOnePublishedBySlug($articleSlug)) {
                    continue;
                }

                $urlToRedirect = $this->router->generate('article_view', [
                    'categorySlug' => $article->getCategory()->getSlug(),
                    'articleSlug' => $article->getSlug(),
                ]);
            }

            if (0 === strpos($requestUri, '/amp/article/')) {
                $articleSlug = substr($requestUri, 13);

                if (!$article = $this->articleRepository->findOnePublishedBySlug($articleSlug)) {
                    continue;
                }

                $urlToRedirect = $this->router->generate('amp_article_view', [
                    'categorySlug' => $article->getCategory()->getSlug(),
                    'articleSlug' => $article->getSlug(),
                ]);
            }

            if (0 === strpos($requestUri, '/amp/proposition/')) {
                $proposalSlug = substr($requestUri, 17);

                if (!$proposal = $this->proposalRepository->findPublishedProposal($proposalSlug)) {
                    continue;
                }

                $urlToRedirect = $this->router->generate('amp_proposal_view', [
                    'slug' => $proposal->getSlug(),
                ]);
            }

            if (0 === strpos($requestUri, '/amp/transformer-la-france/')) {
                $orderArticleSlug = substr($requestUri, 27);

                if (!$orderArticle = $this->orderArticleRepository->findPublishedArticle($orderArticleSlug)) {
                    continue;
                }

                $urlToRedirect = $this->router->generate('amp_explainer_article_show', [
                    'slug' => $orderArticle->getSlug(),
                ]);
            }

            $event->setResponse(new RedirectResponse($urlToRedirect, $redirectCode));

            return;
        }
    }
}
