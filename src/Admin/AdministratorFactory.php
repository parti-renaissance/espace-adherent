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

    /**
     * @param array $data
     * @return Administrator
     */
    public function createFromArray(array $data): Administrator
    {
        $admin = new Administrator();
        $admin->setEmailAddress($data['email']);
        $admin->setPassword($this->encodePassword($data['password']));
        $admin->setGoogleAuthenticatorSecret($data['secret'] ?? null);

        foreach ($data['roles'] as $role) {
            $admin->addRole($role);
        }

        return $admin;
    }

    /**
     * @param string $password
     * @return string
     */
    private function encodePassword(string $password): string
    {
        return $this->encoders->getEncoder(Administrator::class)->encodePassword($password, null);
    }
}
