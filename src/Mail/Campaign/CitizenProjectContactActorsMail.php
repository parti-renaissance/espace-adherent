<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;

final class CitizenProjectContactActorsMail extends CampaignMail
{
    use AdherentMailTrait;

    public static function createRecipients(array $adherents): array
    {
        return array_map(
            function (Adherent $adherent) {
                return self::createRecipientFromAdherent($adherent);
            },
            $adherents
        );
    }

    public static function createTemplateVars(Adherent $host, string $content): array
    {
        return [
            'citizen_project_host_message' => $content,
            'citizen_project_host_firstname' => StringCleaner::htmlspecialchars($host->getFirstName()),
        ];
    }

    public static function createSubject(string $title): string
    {
        return '[Projet citoyen] '.$title;
    }

    public static function createSender(Adherent $author): SenderInterface
    {
        return new Sender(null, $author->getFullName());
    }
}
