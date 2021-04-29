<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\AdherentResetPasswordConfirmationMessage;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentResetPasswordHandler
{
    private $mailer;
    private $manager;
    private $encoderFactory;
    private $dispatcher;

    public function __construct(
        MailerService $transactionalMailer,
        ObjectManager $manager,
        EncoderFactoryInterface $encoderFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->mailer = $transactionalMailer;
        $this->manager = $manager;
        $this->encoderFactory = $encoderFactory;
        $this->dispatcher = $dispatcher;
    }

    public function handle(Adherent $adherent): void
    {
        $resetPasswordToken = AdherentResetPasswordToken::generate($adherent);

        $this->manager->persist($resetPasswordToken);
        $this->manager->flush();

        $this->dispatcher->dispatch(new UserResetPasswordEvent($adherent, $resetPasswordToken), UserEvents::USER_FORGOT_PASSWORD);
    }

    public function reset(Adherent $adherent, AdherentResetPasswordToken $token, string $newPassword): void
    {
        $newEncodedPassword = $this->encoderFactory
            ->getEncoder(Adherent::class)
            ->encodePassword($newPassword, $adherent->getSalt())
        ;

        $token->setNewPassword($newEncodedPassword);
        $adherent->resetPassword($token);

        $this->manager->flush();

        if (null === $adherent->getSource()) {
            $this->mailer->sendMessage(AdherentResetPasswordConfirmationMessage::createFromAdherent($adherent));
        }
    }
}
