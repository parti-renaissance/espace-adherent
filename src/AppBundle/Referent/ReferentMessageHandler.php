<?php

namespace AppBundle\Referent;

use AppBundle\Mailjet\Message\ReferentMessage;
use AppBundle\Producer\Mailjet\ReferentMessageProducerInterface;
use AppBundle\Referent\ReferentMessage as ReferentMessageModel;

class ReferentMessageHandler
{
    private $producer;

    public function __construct(ReferentMessageProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function handle(ReferentMessageModel $model)
    {
        $message = ReferentMessage::createFromModel($model);

        // Split in chunks to avoid overloading Mailjet API
        $chunks = array_chunk($message->getRecipients(), 200);

        foreach ($chunks as $chunk) {
            $this->producer->scheduleMessage(ReferentMessage::createChunk($chunk, $message));
        }
    }
}
