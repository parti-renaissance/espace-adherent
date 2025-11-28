<?php

declare(strict_types=1);

namespace App\AdherentProfile;

use Symfony\Component\Validator\Constraints as Assert;

class Password
{
    /**
     * @var string|null
     */
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'adherent.plain_password.not_blank'),
        new Assert\Length(min: 8, minMessage: 'adherent.plain_password.min_length'),
    ])]
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
