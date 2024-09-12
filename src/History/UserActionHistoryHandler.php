<?php

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\Command\UserActionHistoryCommand;
use App\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;

class UserActionHistoryHandler
{
    public function __construct(
        public readonly AdherentRepository $adherentRepository,
        public readonly RequestStack $requestStack,
        public readonly Security $security,
        public readonly MessageBusInterface $bus,
    ) {
    }

    public function createLoginSuccess(): void
    {
        $user = $this->getUser();

        if (!$user) {
            return;
        }

        $this->dispatch(
            $user,
            UserActionHistoryTypeEnum::LOGIN_SUCCESS
        );
    }

    public function createLoginFailure(): void
    {
        $request = $this->requestStack->getMainRequest();

        if (!$request) {
            return;
        }

        $login = mb_strtolower($request->request->get('_login_email'));

        $adherent = $this->adherentRepository->findOneByEmail($login);

        if (!$adherent) {
            return;
        }

        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::LOGIN_FAILURE
        );
    }

    public function createProfileUpdate(array $properties): void
    {
        $user = $this->getUser();

        if (!$user) {
            return;
        }

        $this->dispatch(
            $user,
            UserActionHistoryTypeEnum::PROFILE_UPDATE,
            $properties,
            $this->getImpersonificator()
        );
    }

    public function createImpersonificationStart(Adherent $adherent): void
    {
        $administrator = $this->security->getUser();

        $this->dispatch(
            $adherent,
            UserActionHistoryTypeEnum::IMPERSONIFICATION_START,
            null,
            $administrator instanceof Administrator ? $administrator : null
        );
    }

    public function createImpersonificationEnd(Administrator $administrator): void
    {
        $user = $this->getUser();

        if (!$user) {
            return;
        }

        $this->dispatch(
            $user,
            UserActionHistoryTypeEnum::IMPERSONIFICATION_END,
            null,
            $administrator
        );
    }

    public function createPasswordResetRequest(Adherent $user): void
    {
        $this->dispatch(
            $user,
            UserActionHistoryTypeEnum::PASSWORD_RESET_REQUEST
        );
    }

    public function createPasswordResetValidate(Adherent $user): void
    {
        $this->dispatch(
            $user,
            UserActionHistoryTypeEnum::PASSWORD_RESET_VALIDATE
        );
    }

    public function createEmailChangeRequest(): void
    {
        $user = $this->getUser();

        if (!$user) {
            return;
        }

        $this->dispatch(
            $user,
            UserActionHistoryTypeEnum::EMAIL_CHANGE_REQUEST,
            null,
            $this->getImpersonificator()
        );
    }

    public function createEmailChangeValidate(): void
    {
        $user = $this->getUser();

        if (!$user) {
            return;
        }

        $this->dispatch(
            $user,
            UserActionHistoryTypeEnum::EMAIL_CHANGE_VALIDATE,
            null,
            $this->getImpersonificator()
        );
    }

    private function getUser(): ?Adherent
    {
        $user = $this->security->getUser();

        return $user instanceof Adherent ? $user : null;
    }

    private function getImpersonificator(): ?Administrator
    {
        $token = $this->security->getToken();

        if (!$token instanceof SwitchUserToken) {
            return null;
        }

        $administrator = $token->getOriginalToken()->getUser();

        return $administrator instanceof Administrator ? $administrator : null;
    }

    private function dispatch(
        Adherent $user,
        UserActionHistoryTypeEnum $type,
        ?array $data = null,
        ?Administrator $administrator = null,
    ): void {
        $this->bus->dispatch(
            new UserActionHistoryCommand(
                $user,
                $type,
                $data,
                $administrator
            )
        );
    }
}
