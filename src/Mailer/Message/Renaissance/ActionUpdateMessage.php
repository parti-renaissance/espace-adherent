<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Action\ActionTypeEnum;
use App\Entity\Action\Action;
use App\Entity\Adherent;
use Symfony\Component\Uid\Uuid;

final class ActionUpdateMessage extends AbstractRenaissanceMessage
{
    /**
     * @param Adherent[] $recipients
     */
    public static function create(array $recipients, Action $action, string $actionUrl): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);

        $message = new self(
            Uuid::v4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            'Une action a été modifiée',
            static::getTemplateVars($action, $actionUrl),
            static::getRecipientVars($recipient)
        );

        /** @var Adherent[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(Action $action, string $actionUrl): array
    {
        return [
            'action_type' => mb_ucfirst(ActionTypeEnum::LABELS[$action->type]),
            'action_date' => static::formatDate($action->date, 'EEEE d MMMM y'),
            'action_hour' => static::formatDate($action->date, 'HH\'h\'mm'),
            'action_address' => self::escape($action->getInlineFormattedAddress()),
            'action_url' => $actionUrl,
        ];
    }

    private static function getRecipientVars(Adherent $recipient): array
    {
        return [
            'target_firstname' => self::escape($recipient->getFirstName()),
        ];
    }
}
