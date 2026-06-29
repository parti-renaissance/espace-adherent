<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessageInterface;

class CampaignPublicationMessage extends AbstractRenaissanceAdherentMessage
{
    protected static function buildTemplateContent(AdherentMessageInterface $message): array
    {
        return ['content' => $message->getContent()];
    }
}
