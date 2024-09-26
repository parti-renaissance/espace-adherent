<?php

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\Command\AdministratorActionHistoryCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class AdministratorActionHistoryHandler
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function createLoginSuccess(Administrator $administrator): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::LOGIN_SUCCESS
        );
    }

    public function createLoginFailure(Administrator $administrator): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::LOGIN_FAILURE
        );
    }

    public function createImpersonationStart(Administrator $administrator, Adherent $adherent): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::IMPERSONATION_START,
            [
                'adherent_uuid' => $adherent->getUuid()->toString(),
            ]
        );
    }

    public function createImpersonationEnd(Adherent $adherent, Administrator $administrator): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::IMPERSONATION_END,
            [
                'adherent_uuid' => $adherent->getUuid()->toString(),
            ]
        );
    }

    public function createExport(Administrator $administrator, string $route, array $parameters): void
    {
        $this->dispatch(
            $administrator,
            AdministratorActionHistoryTypeEnum::EXPORT,
            [
                'route' => $route,
                'parameters' => $parameters,
            ]
        );
    }

    private function dispatch(
        Administrator $administrator,
        AdministratorActionHistoryTypeEnum $type,
        ?array $data = null,
    ): void {
        $this->bus->dispatch(
            new AdministratorActionHistoryCommand(
                $administrator->getId(),
                $type,
                $data
            )
        );
    }
}
