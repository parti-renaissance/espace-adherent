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
            $request->getEmailAddress(),
            null,
            self::getTemplateVars(
                $request->getFirstNames(),
                $proxy->getFirstNames(),
                $proxy->getLastName()
            )
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

    private static function getTemplateVars(
        string $targetFirstName,
        string $voterFirstName,
        string $voterLastName
    ): array {
        return [
            'target_firstname' => self::escape($targetFirstName),
            'voter_first_name' => $voterFirstName,
            'voter_last_name' => $voterLastName,
        ];
    }
}
