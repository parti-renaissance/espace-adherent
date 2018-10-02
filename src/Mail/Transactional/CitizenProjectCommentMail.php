<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProjectComment;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\ChunkableMailInterface;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CitizenProjectCommentMail extends TransactionalMail implements ChunkableMailInterface
{
    use AdherentMailTrait;

    public const SUBJECT = 'Message de votre porteur de projet';

    public static function createRecipientsFrom(array $adherents): array
    {
        return array_map(
            function (Adherent $adherent) {
                return self::createRecipientFromAdherent($adherent);
            },
            $adherents
        );
    }

    public static function createTemplateVars(Adherent $author, CitizenProjectComment $comment): array
    {
        return [
            'citizen_project_host_firstname' => StringCleaner::htmlspecialchars($author->getFirstName()),
            'citizen_project_host_message' => $comment->getContent(),
        ];
    }

    public static function createSender(Adherent $adherent): SenderInterface
    {
        return new Sender(null, $adherent->getFullName());
    }
}
