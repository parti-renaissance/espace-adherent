<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyCancelledMessage extends MailjetMessage
{
    public static function create(Adherent $procurationManager, ProcurationRequest $request, ProcurationProxy $proxy): self
    {
        $message = new static(
            Uuid::uuid4(),
            '120189',
            $request->getEmailAddress(),
            $request->getFirstNames().' '.$request->getLastName(),
            'Annulation de la mise en relation',
            [
                'target_firstname' => self::escape($request->getFirstNames()),
                'voter_first_name' => $proxy->getFirstNames(),
                'voter_last_name' => $proxy->getLastName(),
            ],
            [],
            $procurationManager->getEmailAddress()
        );

        $message->setSenderName('Procuration Macron');
        $message->addCC(sprintf('"%s" <%s>', $procurationManager->getFullName(), $procurationManager->getEmailAddress()));
        $message->addCC(sprintf('"%s %s" <%s>', $proxy->getFirstNames(), $proxy->getLastName(), $proxy->getEmailAddress()));

        return $message;
    }
}
