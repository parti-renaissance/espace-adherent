<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Media;
use Doctrine\ORM\EntityRepository;

class MediaRepository extends EntityRepository
{
    public function findOneByName(string $name): ? Media
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findOneByPath(string $path): ? Media
    {
        return $this->findOneBy(['path' => $path]);
    }
}
