<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Convocation;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use Ramsey\Uuid\Uuid;

final class ReferentInstanceConvocationMessage extends Message
{
    public static function create(Convocation $convocation, Adherent $referent, array $memberships): self
    {
        $instance = $convocation->getEntity();

        $first = array_shift($memberships);
        $adherent = $first->getAdherent();
        $instanceType = $instance instanceof TerritorialCouncil ? 'Conseil territorial' : 'Comité politique';

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            sprintf('[Convocation] %s du %s', $instanceType, self::dateToString($convocation->getMeetingStartDate())),
            [
                'instance_name' => $instance->getName(),
                'instance_type' => $instanceType,
                'now' => self::formatDate(new \DateTime(), 'EEEE d MMMM y'),
                'meeting_start_date' => self::dateToString($convocation->getMeetingStartDate()),
                'online_mode' => $convocation->isOnlineMode(),
                'meeting_url' => $convocation->getMeetingUrl(),
                'address' => $convocation->getInlineFormattedAddress(),
                'description' => $convocation->getDescription(),
                'is_copol' => $instance instanceof PoliticalCommittee,
                'referent_first_name' => $referent->getFirstName(),
                'referent_last_name' => $referent->getLastName(),
                'referent_email' => $referent->getEmailAddress(),
            ],
            [
                'first_name' => $adherent->getFirstName(),
                'last_name' => $adherent->getLastName(),
            ]
        );

        /** @var TerritorialCouncilMembership[]|PoliticalCommitteeMembership[] $memberships */
        foreach ($memberships as $membership) {
            $adherent = $membership->getAdherent();
            $message->addRecipient(
                $adherent->getEmailAddress(),
                $adherent->getFullName(),
                [
                    'first_name' => $adherent->getFirstName(),
                    'last_name' => $adherent->getLastName(),
                ]
            );
        }

        return $message;
    }
}
