<?php

namespace App\Donation;

use App\Entity\DonatorIdentifier;
use App\Repository\DonatorIdentifierRepository;
use Doctrine\ORM\EntityManagerInterface;

class DonatorManager
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly DonatorIdentifierRepository $donatorIdentifierRepository,
    ) {
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
        return \sprintf('%06d', (int) $currentAccountId + 1);
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
