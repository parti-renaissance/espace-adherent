<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Contact\MailchimpCleanableContactInterface;
use App\Mailchimp\Webhook\EventTypeEnum;

class CleanedContactHandler extends AbstractAdherentHandler
{
    public function handle(array $data): void
    {
        $contact = $this->getAdherent($data['email']);

        if (!$contact instanceof MailchimpCleanableContactInterface) {
            return;
        }

        $contact->clean();

        $this->entityManager->flush();
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::CLEANED === $type && parent::support($type, $listId);
    }
}
