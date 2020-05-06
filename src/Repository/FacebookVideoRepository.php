<?php

namespace App\Repository;

use App\Entity\FacebookVideo;
use Doctrine\ORM\EntityRepository;

class FacebookVideoRepository extends EntityRepository
{
    /**
     * @return FacebookVideo[]
     */
    public function findPublishedVideos(): array
    {
        return $this->findBy(['published' => true], ['position' => 'ASC']);
    }
}
