<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Administrator;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class AdministratorFactory
{
    public function __construct(private readonly PasswordHasherFactoryInterface $hasherFactory)
    {
    }

    public function createFromArray(array $data): Administrator
    {
        $admin = new Administrator();
        $admin->setEmailAddress($data['email']);
        $admin->setPassword($this->hashPassword($data['password']));
        $admin->setGoogleAuthenticatorSecret($data['secret'] ?? null);
        if (isset($data['activated'])) {
            $admin->setActivated($data['activated']);
        }

        foreach ($data['roles'] as $role) {
            $admin->addAdministratorRole($role);
        }

        return $admin;
    }

    private function hashPassword(string $password): string
    {
        return $this->hasherFactory->getPasswordHasher(Administrator::class)->hash($password);
    }
}
