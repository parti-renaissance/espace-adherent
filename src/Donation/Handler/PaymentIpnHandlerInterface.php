<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Donation\Command\ReceivePayboxIpnResponseCommand;

interface PaymentIpnHandlerInterface
{
    public function handle(ReceivePayboxIpnResponseCommand $command): void;
}
