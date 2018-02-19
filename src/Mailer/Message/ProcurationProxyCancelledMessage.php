<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyCancelledMessage extends Message
{
    public static function create(ProcurationRequest $request, ?Adherent $referent): self
    {
        $proxy = $request->getFoundProxy();

        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            static::getTemplateVars($request, $proxy)
        );

        $message->setSenderName('Procuration En Marche !');
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

    private static function getTemplateVars(ProcurationRequest $request, ProcurationProxy $proxy): array
    {
        return [
            'target_first_name' => self::escape($request->getFirstNames()),
            'voter_first_name' => $proxy->getFirstNames(),
            'voter_last_name' => $proxy->getLastName(),
        ];
    }
}
