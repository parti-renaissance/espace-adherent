<?php

namespace App\Procuration\V2;

use App\Address\PostAddressFactory;
use App\Entity\Procuration\Proxy;
use App\Entity\Procuration\Request;
use App\Procuration\V2\Command\ProxyCommand;
use App\Procuration\V2\Command\RequestCommand;

class ProcurationFactory
{
    public function __construct(
        private readonly PostAddressFactory $addressFactory
    ) {
    }

    public function createRequestFromCommand(RequestCommand $command): Request
    {
        return new Request(
            $command->email,
            $command->gender,
            $command->firstNames,
            $command->lastName,
            $command->birthdate,
            $this->addressFactory->createFromAddress($command->address),
            $command->distantVotePlace,
            $command->voteZone,
            $command->votePlace,
            $command->adherent,
            $command->clientIp
        );
    }

    public function createProxyFromCommand(ProxyCommand $command): Proxy
    {
        $proxy = new Proxy(
            $command->email,
            $command->gender,
            $command->firstNames,
            $command->lastName,
            $command->birthdate,
            $this->addressFactory->createFromAddress($command->address),
            $command->distantVotePlace,
            $command->voteZone,
            $command->votePlace,
            $command->adherent,
            $command->clientIp
        );

        $proxy->electorNumber = $command->electorNumber;
        $proxy->slots = $command->slots;

        return $proxy;
    }
}
