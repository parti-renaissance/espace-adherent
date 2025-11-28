<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdherentResetPasswordTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdherentResetPasswordTokenRepository::class)]
#[ORM\Table(name: 'adherent_reset_password_tokens')]
#[ORM\UniqueConstraint(name: 'adherent_reset_password_token_unique', columns: ['value'])]
#[ORM\UniqueConstraint(name: 'adherent_reset_password_token_account_unique', columns: ['value', 'adherent_uuid'])]
class AdherentResetPasswordToken extends AdherentToken
{
    /**
     * @var string|null
     */
    private $newPassword;

    /**
     * @return string|null
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword)
    {
        if (null === $this->newPassword) {
            $this->newPassword = $newPassword;
        }
    }

    public function getType(): string
    {
        return 'adherent reset password';
    }
}
