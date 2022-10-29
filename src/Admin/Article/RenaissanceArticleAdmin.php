<?php

namespace App\Admin\Article;

use App\Entity\Article;

class RenaissanceArticleAdmin extends AbstractArticleAdmin
{
    protected $baseRoutePattern = 'renaissance-article';
    protected $baseRouteName = 'renaissance-article';

    /** @param Article $object */
    protected function prePersist(object $object): void
    {
        $object->setForRenaissance(true);
    }

    /** @param Article $object */
    protected function preUpdate(object $object): void
    {
        if (!$object->isForRenaissance()) {
            $object->setForRenaissance(true);
        }
    }

    protected function isRenaissanceArticle(): bool
    {
        return true;
    }
}
