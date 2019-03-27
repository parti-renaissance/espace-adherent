<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyCancelledMessage extends Message
{
    public static function create(ProcurationRequest $request, ?Adherent $referent): self
    {
        $proxy = $request->getFoundProxy();
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

        $message->setSenderName('La République En Marche !');
        $message->addCC($proxy->getEmailAddress());

        $referent = $referent ?: $request->getFoundBy();

        if ($referent) {
            $message->addCC($referent->getEmailAddress());
            $message->setReplyTo($referent->getEmailAddress());
        } else {
            $message->setReplyTo('procurations@en-marche.fr');
        }

        return $message;
    }
}
