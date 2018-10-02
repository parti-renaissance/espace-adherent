<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CommitteeNewFollowerMail extends TransactionalMail
{
    use AdherentMailTrait;

    public const SUBJECT = 'Un nouveau membre vient de suivre votre comité';

    public static function createRecipients(array $adherents): array
    {
        return array_map(
            function (Adherent $adherent) {
                return self::createRecipientFromAdherent(
                    $adherent,
                    ['animator_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
                );
            },
            $adherents
        );
    }

    public static function createTemplateVars(Committee $committee, Adherent $newFollower, string $link): array
    {
        return [
            'committee_name' => StringCleaner::htmlspecialchars($committee->getName()),
            'committee_admin_url' => $link,
            'member_firstname' => StringCleaner::htmlspecialchars($newFollower->getFirstName()),
            'member_lastname' => $newFollower->getLastNameInitial(),
            'member_age' => $newFollower->getAge() ?? 'n/a',
        ];
    }
}
