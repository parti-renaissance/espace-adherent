<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordConfirmationMessage;
use App\Membership\Event\UserEvent;
use App\Membership\Event\UserResetPasswordEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentResetPasswordHandler
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly EntityManagerInterface $manager,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
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
        bool $isCreation = false,
    ): void {
        $token->setNewPassword($this->hasher->hashPassword($adherent, $newPassword));
        $adherent->resetPassword($token);

        $hasBeenActivated = false;
        // activate account if necessary
        if ($adherent->isPending()) {
            $adherent->enable();
            $hasBeenActivated = true;
        }

        $this->manager->flush();

        if (!$isCreation) {
            $this->transactionalMailer->sendMessage(RenaissanceResetPasswordConfirmationMessage::createFromAdherent($adherent));
        } else {
            if ($hasBeenActivated) {
                $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);
            }
        }

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_FORGOT_PASSWORD_VALIDATED);
    }
}
