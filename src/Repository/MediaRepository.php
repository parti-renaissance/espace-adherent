<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MediaRepository extends ServiceEntityRepository
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function findOneByName(string $name): ?Media
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findOneByPath(string $path): ?Media
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
