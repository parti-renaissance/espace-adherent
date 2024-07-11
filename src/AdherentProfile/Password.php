<?php

namespace App\AdherentProfile;

use Symfony\Component\Validator\Constraints as Assert;

class Password
{
    /**
     * @var string|null
     */
    #[Assert\Length(min: 8, minMessage: 'adherent.plain_password.min_length', options: ['allowEmptyString' => true])]
    #[Assert\NotBlank(message: 'adherent.plain_password.not_blank')]
    private $password;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
}
