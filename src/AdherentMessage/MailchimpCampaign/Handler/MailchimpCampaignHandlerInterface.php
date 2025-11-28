<?php

declare(strict_types=1);

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\Entity\AdherentMessage\AdherentMessageInterface;

interface MailchimpCampaignHandlerInterface
{
    public function handle(AdherentMessageInterface $message): void;

    public function supports(AdherentMessageInterface $message): bool;

    public static function getPriority(): int;
}
