<?php

namespace App\Procuration\V2;

use App\Address\PostAddressFactory;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
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
            $command->round,
            $command->email,
            $command->gender,
            $command->firstNames,
            $command->lastName,
            $command->birthdate,
            $command->phone,
            $this->addressFactory->createFromAddress($command->address),
            $command->distantVotePlace,
            $command->voteZone,
            $command->votePlace,
            $command->customVotePlace,
            $command->adherent,
            $command->joinNewsletter,
            $command->clientIp
        );
    }

    public function createProxyFromCommand(ProxyCommand $command): Proxy
    {
        $proxy = new Proxy(
            $command->round,
            $command->email,
            $command->gender,
            $command->firstNames,
            $command->lastName,
            $command->birthdate,
            $command->phone,
            $this->addressFactory->createFromAddress($command->address),
            $command->distantVotePlace,
            $command->voteZone,
            $command->votePlace,
            $command->customVotePlace,
            $command->adherent,
            $command->joinNewsletter,
            $command->clientIp
        );

        $proxy->electorNumber = $command->electorNumber;
        $proxy->slots = $command->slots;

        return $proxy;
    }
}
