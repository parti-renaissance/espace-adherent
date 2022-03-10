<?php

namespace App\Mailer\Message\Procuration;

use App\Entity\ProcurationRequest;
use App\Mailer\Message\Message;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyMatchedMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationRequest $request, string $infosUrl): Message
    {
        $proxy = $request->getFoundProxy();

        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            'Votre procuration',
            [
                'info_link' => $infosUrl,
                'elections' => implode(', ', $request->getElectionRoundLabels()),
                'voter_first_name' => self::escape($proxy->getFirstNames()),
                'voter_last_name' => self::escape($proxy->getLastName()),
                'voter_phone' => PhoneNumberUtils::format($proxy->getPhone()),
                'voter_email' => $proxy->getEmailAddress(),
                'voter_birthdate' => $proxy->getBirthdate() ? self::escape($proxy->getBirthdate()->format('d/m/Y')) : null,
                'voter_vote_place' => self::escape(sprintf('%s (%s)',
                    $proxy->getVoteCityName(),
                    $proxy->getVotePostalCode())
                ),
                'voter_number' => $proxy->getVoterNumber() ? self::escape($proxy->getVoterNumber()) : null,
                'mandant_first_name' => self::escape($request->getFirstNames()),
                'mandant_last_name' => self::escape($request->getLastName()),
                'mandant_phone' => PhoneNumberUtils::format($request->getPhone()),
                'mandant_email' => self::escape($request->getEmailAddress()),
                'mandant_vote_place' => self::escape(sprintf('%s (%s)',
                    $request->getVoteCityName(),
                    $request->getVotePostalCode()
                )),
            ]
        );

        $message->addCC($request->getFoundBy()->getEmailAddress());
        $message->addCC($proxy->getEmailAddress());
        $message->setReplyTo($proxy->getEmailAddress());
        $message->setPreserveRecipients(true);

        return self::updateSenderInfo($message);
    }
}
