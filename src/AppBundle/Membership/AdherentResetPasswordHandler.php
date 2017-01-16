<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\AdherentResetPasswordMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdherentResetPasswordHandler
{
    private $urlGenerator;
    private $mailjet;
    private $manager;
    private $encoderFactory;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        MailjetService $mailjet,
        ObjectManager $manager,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->mailjet = $mailjet;
        $this->manager = $manager;
        $this->encoderFactory = $encoderFactory;
    }

    public function handle(Adherent $adherent)
    {
        $resetPasswordToken = AdherentResetPasswordToken::generate($adherent);

        $this->manager->persist($resetPasswordToken);
        $this->manager->flush();

        $resetPasswordUrl = $this->generateAdherentResetPasswordUrl($adherent, $resetPasswordToken);
        $this->mailjet->sendMessage(AdherentResetPasswordMessage::createFromAdherent($adherent, $resetPasswordUrl));
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
    }

    private function generateAdherentResetPasswordUrl(Adherent $adherent, AdherentResetPasswordToken $token)
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'reset_password_token' => (string) $token->getValue(),
        ];

        return $this->urlGenerator->generate('adherent_forgot_password', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
