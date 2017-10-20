<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Media;
use Doctrine\ORM\EntityRepository;

class MediaRepository extends EntityRepository
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    public function findOneByName(string $name): ? Media
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findOneByPath(string $path): ? Media
    {
        return $this->findOneBy(['path' => $path]);
    }

    /**
     * @return Media[]
     */
    public function findSitemapMedias(string $type): array
    {
        return $this
            ->createQueryBuilder('m')
            ->where('m.mimeType LIKE :type')
            ->setParameter('type', $type.'%')
            ->orderBy('m.id')
            ->getQuery()
            ->getResult()
        ;
    }
}
