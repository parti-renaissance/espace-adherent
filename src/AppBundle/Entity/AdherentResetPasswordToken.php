<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="adherent_reset_password_tokens", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="adherent_reset_password_token_unique", columns="value"),
 *   @ORM\UniqueConstraint(name="adherent_reset_password_token_account_unique", columns={"value", "adherent_uuid"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdherentResetPasswordTokenRepository")
 */
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
