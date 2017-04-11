<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyCancelledMessage extends MailjetMessage
{
    public static function create(?Adherent $procurationManager, ProcurationRequest $request, ProcurationProxy $proxy): self
    {
        $message = new self(
            Uuid::uuid4(),
            '120189',
            $request->getEmailAddress(),
            null,
            'Annulation de la mise en relation',
            [
                'target_firstname' => self::escape($request->getFirstNames()),
                'voter_first_name' => $proxy->getFirstNames(),
                'voter_last_name' => $proxy->getLastName(),
            ]
        );

        $message->setSenderName('Procuration Macron');
        $message->addCC($proxy->getEmailAddress());

        if ($procurationManager) {
            $message->addCC($procurationManager->getEmailAddress());
            $message->setReplyTo($procurationManager->getEmailAddress());
        } else {
            $message->setReplyTo('procurations@en-marche.fr');
        }

        return $message;
    }
}
