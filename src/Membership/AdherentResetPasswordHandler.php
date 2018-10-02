<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Mail\Transactional\AdherentResetPasswordConfirmationMail;
use AppBundle\Mail\Transactional\AdherentResetPasswordMail;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdherentResetPasswordHandler
{
    private $urlGenerator;
    private $mailPost;
    private $manager;
    private $encoderFactory;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        MailPostInterface $mailPost,
        ObjectManager $manager,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->mailPost = $mailPost;
        $this->manager = $manager;
        $this->encoderFactory = $encoderFactory;
    }

    public function handle(Adherent $adherent): void
    {
        $resetPasswordToken = AdherentResetPasswordToken::generate($adherent);

        $this->manager->persist($resetPasswordToken);
        $this->manager->flush();

        $resetPasswordUrl = $this->generateAdherentResetPasswordUrl($adherent, $resetPasswordToken);

        $this->mailPost->address(
            AdherentResetPasswordMail::class,
            AdherentResetPasswordMail::createRecipient($adherent, $resetPasswordUrl)
        );
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

        $this->mailPost->address(
            AdherentResetPasswordConfirmationMail::class,
            AdherentResetPasswordConfirmationMail::createRecipient($adherent)
        );
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
