<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Administrator;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdministratorFactory
{
    private $encoders;

    public function __construct(EncoderFactoryInterface $encoders)
    {
        $this->encoders = $encoders;
    }

    public function createFromArray(array $data): Administrator
    {
        $admin = new Administrator();
        $admin->setEmailAddress($data['email']);
        $admin->setPassword($this->encodePassword($data['password']));
        $admin->setGoogleAuthenticatorSecret($data['secret'] ?? null);
        if (isset($data['activated'])) {
            $admin->setActivated($data['activated']);
        }

        foreach ($data['roles'] as $role) {
            $admin->addRole($role);
        }

        return $admin;
    }

    private function encodePassword(string $password): string
    {
        return $this->encoders->getEncoder(Administrator::class)->encodePassword($password, null);
    }
}
