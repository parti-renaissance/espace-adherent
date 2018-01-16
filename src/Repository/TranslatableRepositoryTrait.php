<?php

namespace AppBundle\Repository;

use Doctrine\ORM\QueryBuilder;

trait TranslatableRepositoryTrait
{
    public function getTranslatedChoicesQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('translatable')
            ->select('translatable, translations')
            ->join('translatable.translations', 'translations')
            ->where('translations.locale = :locale')
            ->setParameter('locale', 'fr')
        ;
    }
}
