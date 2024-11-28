<?php

namespace App\History\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class UserRoleHistoryCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public readonly UuidInterface $userUuid,
        public readonly string $action,
        public readonly string $role,
        public readonly ?int $adminAuthorId = null,
        public readonly ?UuidInterface $userAuthorUuid = null,
    ) {
    }
}
