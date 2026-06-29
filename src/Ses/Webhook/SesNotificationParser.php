<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesNotificationParser
{
    public function parse(array $snsPayload): ?SesFeedbackEvent
    {
        $event = $this->decodeSesEvent($snsPayload);
        if (null === $event) {
            return null;
        }

        $type = $this->feedbackType($event);
        if (null === $type) {
            return null;
        }

        $recipients = $this->extractRecipients($event, $type);
        if ([] === $recipients) {
            // Actionable feedback whose recipients could not be read: see describesFeedback() (format drift).
            return null;
        }

        return new SesFeedbackEvent($type, $recipients);
    }

    public function describesFeedback(array $snsPayload): bool
    {
        $event = $this->decodeSesEvent($snsPayload);

        return null !== $event && null !== $this->feedbackType($event);
    }

    private function decodeSesEvent(array $snsPayload): ?array
    {
        $message = $snsPayload['Message'] ?? null;
        if (!\is_string($message)) {
            return null;
        }

        $decoded = json_decode($message, true);

        return \is_array($decoded) ? $decoded : null;
    }

    private function feedbackType(array $event): ?SesFeedbackType
    {
        // Event publishing uses "eventType"; the direct identity notification uses "notificationType".
        $kind = $event['eventType'] ?? $event['notificationType'] ?? null;

        return match ($kind) {
            'Bounce' => 'Permanent' === ($event['bounce']['bounceType'] ?? null) ? SesFeedbackType::HARD_BOUNCE : null,
            'Complaint' => SesFeedbackType::COMPLAINT,
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    private function extractRecipients(array $event, SesFeedbackType $type): array
    {
        if (SesFeedbackType::HARD_BOUNCE === $type) {
            $rawRecipients = $event['bounce']['bouncedRecipients'] ?? [];
        } else {
            $rawRecipients = $event['complaint']['complainedRecipients'] ?? [];
        }

        $recipients = [];
        foreach ($rawRecipients as $recipient) {
            $email = $recipient['emailAddress'] ?? null;
            if (\is_string($email) && '' !== $email) {
                $recipients[] = $email;
            }
        }

        return $recipients;
    }
}
