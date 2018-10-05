<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class CommitteeNewFollowerMail extends TransactionalMail
{
    use AdherentMailTrait;

    const SUBJECT = 'Un nouveau membre vient de suivre votre comité';

    public static function createRecipientsFrom(AdherentCollection $hosts): array
    {
        return $hosts
            ->map(function (Adherent $host){
                return self::createRecipientFromAdherent($host, [
                    'animator_firstname' => StringCleaner::htmlspecialchars($host->getFullName()),
                ]);
            })
            ->toArray();
    }

    public static function createTemplateVarsFrom(Committee $committee, Adherent $newFollower, string $hostUrl): array
    {
        return [
            'committee_name' => StringCleaner::htmlspecialchars($committee->getName()),
            'committee_admin_url' => $hostUrl,
            'member_firstname' => StringCleaner::htmlspecialchars($newFollower->getFirstName()),
            'member_lastname' => $newFollower->getLastNameInitial(),
            'member_age' => $newFollower->getAge() ?? 'n/a',
        ];
    }
}
