<?php

namespace App\Repository\Ohme;

use App\Entity\Ohme\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function findOneByOhmeIdentifier(string $identifier): ?Contact
    {
        return $this
            ->createQueryBuilder('contact')
            ->andWhere('contact.identifier = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function refresh(Contact $contact): void
    {
        $this->_em->refresh($contact);
    }

    public function save(Contact $contact): void
    {
        $this->_em->persist($contact);
        $this->_em->flush();
    }
}
