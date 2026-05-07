<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

class PrepareResult
{
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_CONFLICT = 'conflict';

    private function __construct(
        public string $status,
        public array $sendStatus,
    ) {
    }

    public static function preparing(array $sendStatus): self
    {
        return new self(self::STATUS_PREPARING, $sendStatus);
    }

    public static function conflict(array $sendStatus): self
    {
        return new self(self::STATUS_CONFLICT, $sendStatus);
    }

    public function isConflict(): bool
    {
        return self::STATUS_CONFLICT === $this->status;
    }

    public function isPreparing(): bool
    {
        return self::STATUS_PREPARING === $this->status;
    }

    public function toApiPayload(): array
    {
        return [
            'status' => $this->status,
            'send_status' => $this->sendStatus,
        ];
    }
}
