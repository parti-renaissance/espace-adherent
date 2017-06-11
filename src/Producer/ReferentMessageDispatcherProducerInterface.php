<?php

namespace AppBundle\Producer;

use AppBundle\Referent\ReferentMessage;

interface ReferentMessageDispatcherProducerInterface
{
    public function scheduleDispatch(ReferentMessage $message): void;
}
