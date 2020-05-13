<?php

namespace App\Repository;

use App\Entity\Adherent;

trait AuthorTrait
{
    public function removeAuthorItems(Adherent $author): void
    {
        $this->createQueryBuilder('i')
            ->delete()
            ->where('i.author = :author')
            ->setParameter(':author', $author)
            ->getQuery()
            ->execute()
        ;
    }
}
