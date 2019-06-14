<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use Ramsey\Uuid\Uuid;

final class ApplicationRequestConfirmationMessage extends Message
{
    public static function create(ApplicationRequest $applicationRequest, string $applicationRequestUrl): self
    {
        return new self(
            Uuid::uuid4(),
            '875384',
            $applicationRequest->getEmailAddress(),
            $applicationRequest->getFullName(),
            'Merci pour votre candidature !',
            static::getTemplateVars($applicationRequestUrl),
            static::getRecipientVars($applicationRequest->getFirstName())
        );
    }

    private static function getTemplateVars(string $applicationRequestUrl): array
    {
        return [
            'application_request_url' => $applicationRequestUrl,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
