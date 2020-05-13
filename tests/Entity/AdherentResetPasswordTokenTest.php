<?php

namespace Tests\App\Entity;

use App\Entity\AdherentResetPasswordToken;

class AdherentResetPasswordTokenTest extends AbstractAdherentTokenTest
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Token must have a new password.
     */
    public function testUseResetPasswordTokenFailWithoutPassword()
    {
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
