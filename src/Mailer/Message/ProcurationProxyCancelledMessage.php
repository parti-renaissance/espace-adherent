<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyCancelledMessage extends Message
{
    public static function create(ProcurationRequest $request, ProcurationProxy $proxy, ?Adherent $procurationManager): self
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

        $message->setSenderName('Procuration En Marche !');
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
