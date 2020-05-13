<?php

namespace App\EntityListener;

use App\Entity\Article;
use App\Redirection\Dynamic\RedirectionManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class ArticleListener
{
    private $redirectionManager;
    private $router;

    /**
     * @var array[]
     */
    private $redirections = [];

    public function __construct(RedirectionManager $redirectionManager, Router $router)
    {
        $this->redirectionManager = $redirectionManager;
        $this->router = $router;
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
            'source' => $this->router->generate('article_view',
                [
                    'articleSlug' => $articleSlugOld,
                    'categorySlug' => $categoryOld->getSlug(),
                ]),
            'target' => $this->router->generate('article_view',
                [
                    'articleSlug' => $articleSlugNew,
                    'categorySlug' => $categoryNew->getSlug(),
                ]),
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
}
