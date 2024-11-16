<?php

namespace App\Membership;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdherentChangePasswordHandler
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly UserPasswordHasherInterface $encoder,
    ) {
    }

    public function changePassword(Adherent $adherent, string $newPassword): void
    {
        $adherent->changePassword($this->encoder->hashPassword($adherent, $newPassword));

        $this->manager->flush();
    }
}
