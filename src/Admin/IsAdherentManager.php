<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Adherent;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IsAdherentManager
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function isAdherent(string $email): bool
    {
        return $this->registry
            ->getRepository(Adherent::class)
            ->isAdherent($email)
        ;
    }
}
