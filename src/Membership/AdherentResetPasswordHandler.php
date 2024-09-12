<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordConfirmationMessage;
use App\Membership\Event\UserEvent;
use App\Membership\Event\UserResetPasswordEvent;
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
        EventDispatcherInterface $dispatcher,
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

        $this->dispatcher->dispatch(new UserResetPasswordEvent($adherent, $resetPasswordToken, MembershipSourceEnum::RENAISSANCE), UserEvents::USER_FORGOT_PASSWORD);
    }

    public function reset(
        Adherent $adherent,
        AdherentResetPasswordToken $token,
        string $newPassword,
        ?string $appCode = null,
        bool $isCreation = false,
    ): void {
        $newEncodedPassword = $this->encoderFactory
            ->getEncoder(Adherent::class)
            ->encodePassword($newPassword, $adherent->getSalt())
        ;

        $token->setNewPassword($newEncodedPassword);
        $adherent->resetPassword($token);

        $hasBeenActivated = false;
        if ($adherent->getSource()) {
            // activate account if necessary
            if (!$adherent->getActivatedAt()) {
                $adherent->activate(AdherentActivationToken::generate($adherent));
                $hasBeenActivated = true;
            }
        }

        $this->manager->flush();

        if (!$isCreation) {
            $this->mailer->sendMessage(RenaissanceResetPasswordConfirmationMessage::createFromAdherent($adherent));
        } else {
            if ($hasBeenActivated) {
                $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);
            }
        }

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_FORGOT_PASSWORD_VALIDATED);
    }
}
