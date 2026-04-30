<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

use App\Messenger\Message\CronjobMessageInterface;
use App\Messenger\Message\LockableMessageInterface;

readonly class PopulateAdherentActivityCommand implements CronjobMessageInterface, LockableMessageInterface
{
    public function __construct(
        public SourceTypeEnum $sourceType = SourceTypeEnum::ActionHistory,
    ) {
    }

    public function getLockKey(): string
    {
        return 'populate_adherent_activity';
    }

    public function getLockTtl(): int
    {
        return 600;
    }

    public function isLockBlocking(): bool
    {
        return true;
    }
}
