<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\AdherentResetPasswordToken;

class AdherentResetPasswordTokenTest extends AbstractAdherentTokenTestCase
{
    protected $tokenClass = AdherentResetPasswordToken::class;

    public function testUseResetPasswordTokenIsSuccessful()
    {
        $adherent = $this->createAdherent();
        $token = AdherentResetPasswordToken::generate($adherent);
        $newPassword = 'pass';

        $this->assertNotSame($newPassword, $adherent->getPassword());

        $token->setNewPassword($newPassword);
        $adherent->resetPassword($token);

        $this->assertSame($newPassword, $adherent->getPassword());
        $this->assertInstanceOf(\DateTime::class, $token->getUsageDate());
    }

    public function testUseResetPasswordTokenFailWithoutPassword()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token must have a new password.');

        $adherent = $this->createAdherent();
        $token = AdherentResetPasswordToken::generate($adherent);

        $adherent->resetPassword($token);
    }

    public function testSetNewPasswordWorksOnlyOnce()
    {
        $adherent = $this->createAdherent();
        $token = AdherentResetPasswordToken::generate($adherent);
        $newPassword = 'pass';

        $this->assertNull($token->getNewPassword());

        $token->setNewPassword($newPassword);

        $this->assertSame($newPassword, $token->getNewPassword());

        // Should not change
        $token->setNewPassword('toto');

        $this->assertSame($newPassword, $token->getNewPassword(), 'The new password should only be set once.');
    }
}
