<?php

namespace App\AdherentProfile;

use App\Entity\Adherent;
use App\Validator\NewUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

#[NewUserPassword]
class PasswordChangeRequest implements NewUserPasswordInterface
{
    #[Assert\NotBlank]
    #[UserPassword(message: 'adherent.wrong_password')]
    public ?string $oldPassword;

    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit faire au moins 8 caractères.')]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/[a-z]+/', message: 'Le mot de passe doit contenir au moins une lettre minuscule.')]
    #[Assert\Regex(pattern: '/[A-Z]+/', message: 'Le mot de passe doit contenir au moins une lettre majuscule.')]
    #[Assert\Regex(pattern: '/[\!\@\#\$\%\^\&\*\(\)\-\_\=\+\{\}\|\:\;\"\'\<\>\,\.\?\[\]\\\\\/]+/i', message: 'Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*()-_=+{}|:;"\'<>,.?[]\/).')]
    public ?string $newPassword = null;

    #[Assert\NotBlank]
    public ?string $newPasswordConfirmation = null;

    public function __construct(Adherent $adherent)
    {
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function getNewPasswordConfirmation(): ?string
    {
        return $this->newPasswordConfirmation;
    }
}
