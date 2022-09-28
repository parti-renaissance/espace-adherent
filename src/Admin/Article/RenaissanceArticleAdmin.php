<?php

namespace App\Admin\Article;

use App\Entity\Article;

class RenaissanceArticleAdmin extends AbstractArticleAdmin
{
    protected $baseRoutePattern = 'renaissance-article';
    protected $baseRouteName = 'renaissance-article';

    /** @param Article $object */
    public function prePersist($object)
    {
        $object->setForRenaissance(true);
    }

    /** @param Article $object */
    public function preUpdate($object)
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
