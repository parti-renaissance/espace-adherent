<?php

declare(strict_types=1);

namespace App\Membership;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdherentChangePasswordHandler
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function changePassword(Adherent $adherent, string $newPassword): void
    {
        $adherent->changePassword($this->hasher->hashPassword($adherent, $newPassword));

        $this->manager->flush();
    }
}
