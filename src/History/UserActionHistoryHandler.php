<?php

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\Command\UserActionHistoryCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;

class UserActionHistoryHandler
{
    public function __construct(
        public readonly Security $security,
        public readonly MessageBusInterface $bus,
    ) {
    }

    public function createLoginSuccess(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::LOGIN_SUCCESS
        );
    }

    public function createLoginFailure(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::LOGIN_FAILURE
        );
    }

    public function createProfileUpdate(Adherent $adherent, array $properties): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::PROFILE_UPDATE,
            $properties,
            $this->getImpersonator()
        );
    }

    public function createImpersonificationStart(Administrator $administrator, Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::IMPERSONIFICATION_START,
            null,
            $administrator
        );
    }

    public function createImpersonificationEnd(Adherent $adherent, Administrator $administrator): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::IMPERSONIFICATION_END,
            null,
            $administrator
        );
    }

    public function createPasswordResetRequest(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::PASSWORD_RESET_REQUEST
        );
    }

    public function createPasswordResetValidate(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::PASSWORD_RESET_VALIDATE
        );
    }

    public function createEmailChangeRequest(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::EMAIL_CHANGE_REQUEST,
            null,
            $this->getImpersonator()
        );
    }

    public function createEmailChangeValidate(Adherent $adherent): void
    {
        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::EMAIL_CHANGE_VALIDATE,
            null,
            $this->getImpersonator()
        );
    }

    private function getImpersonator(): ?Administrator
    {
        $token = $this->security->getToken();

        if (!$token instanceof SwitchUserToken) {
            return null;
        }

        $administrator = $token->getOriginalToken()->getUser();

        return $administrator instanceof Administrator ? $administrator : null;
    }

    private function dispatch(
        Adherent $adherent,
        UserActionHistoryTypeEnum $type,
        ?array $data = null,
        ?Administrator $administrator = null,
    ): void {
        $this->bus->dispatch(
            new UserActionHistoryCommand(
                $adherent->getUuid(),
                $type,
                $data,
                $administrator?->getId()
            )
        );
    }
}
