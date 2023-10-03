<?php

namespace App\Repository\Renaissance;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentRequest::class);
    }

    public function findOneForEmail(string $email, string $source): ?AdherentRequest
    {
        return $this->createQueryBuilder('adherent_request')
            ->where('adherent_request.email = :email AND adherent_request.tokenUsedAt IS NULL AND adherent_request.utmSource = :source')
            ->setParameters([
                'email' => $email,
                'source' => $source,
            ])
            ->orderBy('adherent_request.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }
}
