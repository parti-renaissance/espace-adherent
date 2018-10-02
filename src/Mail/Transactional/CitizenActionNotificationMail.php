<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\DateTimeFormatter;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\ChunkableMailInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CitizenActionNotificationMail extends TransactionalMail implements ChunkableMailInterface
{
    use AdherentMailTrait;

    public const SUBJECT = '[Projets citoyens] Une nouvelle action citoyenne au sein de votre projet citoyen !';

    public static function createRecipients(array $followers): array
    {
        return array_map(function (Adherent $adherent) {
            return self::createRecipientFromAdherent(
                $adherent,
                ['first_name' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
            );
        }, $followers);
    }

    public static function createTemplateVars(CitizenAction $action, string $link): array
    {
        return [
            'host_first_name' => StringCleaner::htmlspecialchars($action->getOrganizerName()),
            'citizen_project_name' => StringCleaner::htmlspecialchars($action->getCitizenProject()->getName()),
            'citizen_action_name' => StringCleaner::htmlspecialchars($action->getName()),
            'citizen_action_date' => DateTimeFormatter::formatDate($action->getBeginAt(), 'EEEE d MMMM y'),
            'citizen_action_hour' => sprintf(
                '%sh%s',
                DateTimeFormatter::formatDate($action->getBeginAt(), 'HH'),
                DateTimeFormatter::formatDate($action->getBeginAt(), 'mm')
            ),
            'citizen_action_address' => StringCleaner::htmlspecialchars($action->getInlineFormattedAddress()),
            'citizen_action_attend_link' => $link,
        ];
    }
}
