<?php

declare(strict_types=1);

namespace App\Procuration\V2;

use App\Address\PostAddressFactory;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\ProxySlot;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\RequestSlot;
use App\Procuration\V2\Command\ProxyCommand;
use App\Procuration\V2\Command\RequestCommand;
use Ramsey\Uuid\Uuid;

class ProcurationFactory
{
    public function __construct(
        private readonly PostAddressFactory $addressFactory,
    ) {
    }

    public function createRequestFromCommand(RequestCommand $command): Request
    {
        $request = new Request(
            Uuid::uuid4(),
            $command->rounds->toArray(),
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
            $command->fromFrance,
            $command->adherent,
            $command->joinNewsletter,
            $command->clientIp
        );

        foreach ($command->rounds as $round) {
            $request->requestSlots->add(new RequestSlot($round, $request));
        }

        return $request;
    }

    public function createProxyFromCommand(ProxyCommand $command): Proxy
    {
        $proxy = new Proxy(
            Uuid::uuid4(),
            $command->rounds->toArray(),
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

        $proxy->acceptVoteNearby = (bool) $command->acceptVoteNearby;
        $proxy->electorNumber = $command->electorNumber;
        $proxy->slots = $command->slots;

        foreach ($command->rounds as $round) {
            for ($i = 1; $i <= $command->slots; ++$i) {
                $proxy->proxySlots->add(new ProxySlot($round, $proxy));
            }
        }

        return $proxy;
    }
}
