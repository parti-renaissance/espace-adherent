<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Mailer\Message\Message;
use Symfony\Component\Uid\Uuid;

final class PronosticCreationMessage extends AbstractRenaissanceMessage
{
    /**
     * @param Adherent[] $recipients
     */
    public static function create(Pronostic $pronostic, array $recipients): Message
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $vars = [
            'pronostic_title' => self::escape($pronostic->title),
            'team_1' => self::escape($pronostic->team1),
            'team_2' => self::escape($pronostic->team2),
            'match_date' => static::formatDate($pronostic->matchAt, 'EEEE d MMMM y'),
            'match_hour' => static::formatDate($pronostic->matchAt, 'HH\'h\'mm'),
        ];

        $message = new self(
            Uuid::v4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            \sprintf('Nouveau pronostic : %s', $pronostic->title),
            $vars,
            self::getRecipientVars($recipient->getFirstName()),
        );

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                self::getRecipientVars($recipient->getFirstName()),
            );
        }

        return self::updateSenderInfo($message);
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
