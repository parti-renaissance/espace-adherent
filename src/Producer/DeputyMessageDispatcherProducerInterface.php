<?php

namespace AppBundle\Producer;

use AppBundle\Deputy\DeputyMessage;

interface DeputyMessageDispatcherProducerInterface
{
    public function scheduleDispatch(DeputyMessage $message): void;
}
