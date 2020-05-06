<?php

namespace App\Membership;

use App\Entity\Adherent;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdherentChangePasswordHandler
{
    private $manager;
    private $encoder;

    public function __construct(ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
    }

    public function changePassword(Adherent $adherent, string $newPassword)
    {
        $adherent->changePassword($this->encoder->encodePassword($adherent, $newPassword));

        $this->manager->flush();
    }
}
