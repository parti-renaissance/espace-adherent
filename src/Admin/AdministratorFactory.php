<?php

namespace App\Admin;

use App\Entity\Administrator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdministratorFactory
{
    public function __construct(private readonly UserPasswordHasherInterface $encoder)
    {
    }

    public function createFromArray(array $data): Administrator
    {
        $admin = new Administrator();
        $admin->setEmailAddress($data['email']);
        $admin->setPassword($this->encoder->hashPassword($admin, $data['password']));
        $admin->setGoogleAuthenticatorSecret($data['secret'] ?? null);
        if (isset($data['activated'])) {
            $admin->setActivated($data['activated']);
        }

        foreach ($data['roles'] as $role) {
            $admin->addAdministratorRole($role);
        }

        return $admin;
    }
}
