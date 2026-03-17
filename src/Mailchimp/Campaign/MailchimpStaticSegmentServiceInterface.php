<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

interface MailchimpStaticSegmentServiceInterface
{
    public function create(string $name, array $emails = [], ?string $listId = null): ?int;

    public function update(int $segmentId, array $emails, ?string $listId = null): bool;

    public function createOrUpdate(string $name, array $emails, ?int $segmentId = null, ?string $listId = null): int|bool|null;
}
