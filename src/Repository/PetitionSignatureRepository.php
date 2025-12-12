<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\PetitionSignature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class PetitionSignatureRepository extends ServiceEntityRepository implements UpdateAdherentLinkRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PetitionSignature::class);
    }

    /**
     * @return PetitionSignature[]
     */
    public function findAllToRemind(): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.validatedAt IS NULL AND ps.remindedAt IS NULL')
            ->andWhere('ps.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 week'))
            ->getQuery()
            ->getResult()
        ;
    }

    /** @param PetitionSignature $object */
    public function updateAdherentLink(object $object): void
    {
        if ($object->adherent) {
            return;
        }

        $object->adherent = $this->getEntityManager()->getRepository(Adherent::class)->findOneBy([
            'emailAddress' => $object->emailAddress,
            'firstName' => $object->firstName,
            'lastName' => $object->lastName,
        ]);
    }

    public function updateLinksWithNewAdherent(Adherent $adherent): void
    {
        $this->createQueryBuilder('ps')
            ->update()
            ->set('ps.adherent', ':adherent')
            ->where('ps.adherent IS NULL')
            ->andWhere('ps.emailAddress = :email')
            ->andWhere('ps.firstName = :first_name')
            ->andWhere('ps.lastName = :last_name')
            ->setParameters(new ArrayCollection([
                new Parameter('adherent', $adherent),
                new Parameter('email', $adherent->getEmailAddress()),
                new Parameter('first_name', $adherent->getFirstName()),
                new Parameter('last_name', $adherent->getLastName()),
            ]))
            ->getQuery()
            ->execute()
        ;
    }
}
