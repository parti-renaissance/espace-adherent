<?php

namespace App\Repository\Filesystem;

use App\Entity\Filesystem\File;
use App\Entity\Filesystem\FileTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function findForAutocomplete(string $search): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.type = :directory')
            ->andWhere('f.displayed = 1')
            ->andWhere('LOWER(f.name) LIKE :search')
            ->setParameter('directory', FileTypeEnum::DIRECTORY)
            ->setParameter('search', '%'.$search.'%')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDirectoryByName($value): ?File
    {
        $name = \is_array($value) ? $value['name'] : $value;

        return $this->createQueryBuilder('file')
            ->where('file.type = :dir_type AND file.name = :name')
            ->setParameters([
                'name' => $name,
                'dir_type' => FileTypeEnum::DIRECTORY,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
