<?php

namespace AppBundle\Donation;

use AppBundle\Repository\DonatorIdentifierRepository;
use Doctrine\Common\Persistence\ObjectManager;

class DonatorManager
{
    private $manager;
    private $donatorIdentifierRepository;

    public function __construct(ObjectManager $manager, DonatorIdentifierRepository $donatorIdentifierRepository)
    {
        $this->manager = $manager;
        $this->donatorIdentifierRepository = $donatorIdentifierRepository;
    }

    public function incrementeIdentifier(bool $flush = true): ?string
    {
        $identifier = $this->donatorIdentifierRepository->findLastIdentifier();
        $identifier->setIdentifier($this->getNextAccountId($identifier->getIdentifier()));

        if ($flush) {
            $this->manager->flush();
        }

        return $identifier->getIdentifier();
    }

    private function getNextAccountId(string $currentAccountId): string
    {
        return str_pad((int) ($currentAccountId) + 1, 6, '0', \STR_PAD_LEFT);
    }
}
