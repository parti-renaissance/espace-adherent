<?php

namespace AppBundle\Referent;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\ReferentMessage;
use AppBundle\Referent\ReferentMessage as ReferentMessageModel;

class ReferentMessageHandler
{
    private $mailjet;

    public function __construct(MailjetService $mailjet)
    {
        $this->mailjet = $mailjet;
    }

    public function handle(ReferentMessageModel $model)
    {
        $message = ReferentMessage::createFromModel($model);

        // Split in chunks to avoid overloading Mailjet API
        $chunks = array_chunk($message->getRecipients(), 100);

        foreach ($chunks as $chunk) {
            $this->mailjet->sendMessage(ReferentMessage::createChunk($chunk, $message));
        }
    }
}
