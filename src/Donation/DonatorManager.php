<?php

namespace App\Donation;

use App\Entity\DonatorIdentifier;
use App\Repository\DonatorIdentifierRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;

class DonatorManager
{
    private $manager;
    private $donatorIdentifierRepository;

    public function __construct(ObjectManager $manager, DonatorIdentifierRepository $donatorIdentifierRepository)
    {
        $this->manager = $manager;
        $this->donatorIdentifierRepository = $donatorIdentifierRepository;
    }

    public function incrementIdentifier(bool $flush = true): ?string
    {
        $identifier = $this->findLastIdentifier();
        $identifier->setIdentifier($this->getNextAccountId($identifier->getIdentifier()));

        if ($flush) {
            $this->manager->flush();
        }

        return $identifier->getIdentifier();
    }

    public function updateIdentifier(string $identifier, bool $flush = true): void
    {
        $lastIdentifier = $this->findLastIdentifier();
        $lastIdentifier->setIdentifier($identifier);

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function getNextAccountId(string $currentAccountId): string
    {
        return str_pad((int) $currentAccountId + 1, 6, '0', \STR_PAD_LEFT);
    }

    public function findLastIdentifier(): DonatorIdentifier
    {
        if (!$identifier = $this->donatorIdentifierRepository->findLastIdentifier()) {
            $identifier = new DonatorIdentifier();
            $identifier->setIdentifier('000001');

            $this->manager->persist($identifier);
            $this->manager->flush();
        }

        return $identifier;
    }
}
