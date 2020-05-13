<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\AdherentResetPasswordConfirmationMessage;
use App\Mailer\Message\AdherentResetPasswordMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdherentResetPasswordHandler
{
    private $urlGenerator;
    private $mailer;
    private $manager;
    private $encoderFactory;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        MailerService $mailer,
        ObjectManager $manager,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->encoderFactory = $encoderFactory;
    }

    public function handle(Adherent $adherent)
    {
        $resetPasswordToken = AdherentResetPasswordToken::generate($adherent);

        $this->manager->persist($resetPasswordToken);
        $this->manager->flush();

        $resetPasswordUrl = $this->generateAdherentResetPasswordUrl($adherent, $resetPasswordToken);
        $this->mailer->sendMessage(AdherentResetPasswordMessage::createFromAdherent($adherent, $resetPasswordUrl));
    }

    public function reset(Adherent $adherent, AdherentResetPasswordToken $token, string $newPassword)
    {
        $newEncodedPassword = $this->encoderFactory
            ->getEncoder(Adherent::class)
            ->encodePassword($newPassword, $adherent->getSalt())
        ;

        $token->setNewPassword($newEncodedPassword);
        $adherent->resetPassword($token);

        $this->manager->flush();

        $this->mailer->sendMessage(AdherentResetPasswordConfirmationMessage::createFromAdherent($adherent));
    }

    private function generateAdherentResetPasswordUrl(Adherent $adherent, AdherentResetPasswordToken $token)
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'reset_password_token' => (string) $token->getValue(),
        ];

        return $this->urlGenerator->generate('adherent_reset_password', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
