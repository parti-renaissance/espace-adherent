<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Action\ActionTypeEnum;
use App\Entity\Action\Action;
use App\Entity\Adherent;
use Symfony\Component\Uid\Uuid;

final class ActionNotificationMessage extends AbstractRenaissanceMessage
{
    /**
     * @param Adherent[] $recipients
     */
    public static function create(array $recipients, Adherent $host, Action $action, string $actionUrl): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $vars = [
            'author_firstname' => self::escape($host->getFirstName()),
            'action_type' => mb_ucfirst(ActionTypeEnum::LABELS[$action->type]),
            'action_date' => static::formatDate($action->date, 'EEEE d MMMM y'),
            'action_hour' => static::formatDate($action->date, 'HH\'h\'mm'),
            'action_address' => self::escape($action->getInlineFormattedAddress()),
            'action_url' => $actionUrl,
            'action_description' => $action->description,
        ];

        $message = new self(
            Uuid::v4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            \sprintf(
                '%s : nouvelle action de %s à %s',
                static::formatDate($action->date, 'd MMMM'),
                ActionTypeEnum::LABELS[$action->type],
                $action->getCityName()
            ),
            $vars,
            static::getRecipientVars($recipient->getFirstName()),
            $host->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient->getFirstName())
            );
        }

        return $message;
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
