<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AdherentMessageRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractAdherentMessage::class);
    }

    public function findAllByAuthor(Adherent $adherent, string $status = null): array
    {
        return $this->findBy(array_merge(
            ['author' => $adherent],
            $status ? ['status' => $status] : []
        ));
    }
}
