<?php

namespace App\EntityListener;

use App\Entity\Article;
use App\Redirection\Dynamic\RedirectionManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArticleListener
{
    private array $redirections = [];

    public function __construct(
        private readonly RedirectionManager $redirectionManager,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public function preUpdate(Article $article, PreUpdateEventArgs $preUpdateEventArgs): void
    {
        if (!$article->isPublished()
            || (!$preUpdateEventArgs->hasChangedField('slug')
                && !$preUpdateEventArgs->hasChangedField('category'))
        ) {
            return;
        }

        $articleSlugNew = $articleSlugOld = $article->getSlug();
        if ($preUpdateEventArgs->hasChangedField('slug')) {
            $articleSlugOld = $preUpdateEventArgs->getOldValue('slug');
            $articleSlugNew = $preUpdateEventArgs->getNewValue('slug');
        }
        $categoryNew = $categoryOld = $article->getCategory();
        if ($preUpdateEventArgs->hasChangedField('category')) {
            $articleSlugOld = $preUpdateEventArgs->getOldValue('category');
            $articleSlugNew = $preUpdateEventArgs->getNewValue('category');
        }

        $this->redirections[$article->getId()] = [
            'source' => $this->parseUrl($this->router->generate('article_view',
                [
                    'articleSlug' => $articleSlugOld,
                    'categorySlug' => $categoryOld->getSlug(),
                ])),
            'target' => $this->parseUrl($this->router->generate('article_view',
                [
                    'articleSlug' => $articleSlugNew,
                    'categorySlug' => $categoryNew->getSlug(),
                ])),
        ];
    }

    public function postUpdate(Article $article): void
    {
        if (isset($this->redirections[$article->getId()])) {
            $redirection = $this->redirectionManager->setRedirection(
                $this->redirections[$article->getId()]['source'],
                $this->redirections[$article->getId()]['target']
            );

            $this->redirectionManager->optimiseRedirection($redirection);
        }
    }

    private function parseUrl(string $url): string
    {
        return parse_url($url, \PHP_URL_PATH);
    }
}
