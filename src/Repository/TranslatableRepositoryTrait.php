<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

trait TranslatableRepositoryTrait
{
    public function createTranslatedChoicesQueryBuilder(string $alias = 'translatable'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->select("$alias, translations")
            ->join("$alias.translations", 'translations')
            ->where('translations.locale = :locale')
            ->setParameter('locale', 'fr')
        ;
    }
}
