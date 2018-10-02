<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CitizenProjectNewFollowerMail extends TransactionalMail
{
    use AdherentMailTrait;

    public const SUBJECT = 'Un nouveau membre a rejoint votre projet citoyen !';

    public static function createRecipientsFrom(array $adherents): array
    {
        return array_map(
            function (Adherent $adherent) {
                return self::createRecipientFromAdherent($adherent);
            },
            $adherents
        );
    }

    public static function createTemplateVars(CitizenProject $citizenProject, Adherent $follower): array
    {
        return [
            'citizen_project_name' => StringCleaner::htmlspecialchars($citizenProject->getName()),
            'follower_firstname' => StringCleaner::htmlspecialchars($follower->getFirstName()),
            'follower_lastname' => $follower->getLastNameInitial(),
            'follower_age' => $follower->getAge() ?? 'n/a',
            'follower_city' => $follower->getCityName() ?? 'n/a',
        ];
    }
}
