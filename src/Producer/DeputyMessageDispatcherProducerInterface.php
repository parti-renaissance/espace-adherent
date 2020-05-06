<?php

namespace App\Producer;

use App\Deputy\DeputyMessage;

interface DeputyMessageDispatcherProducerInterface
{
    public function scheduleDispatch(DeputyMessage $message): void;
}
