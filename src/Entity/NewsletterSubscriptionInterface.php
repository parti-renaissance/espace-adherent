<?php

namespace App\Entity;

use Ramsey\Uuid\UuidInterface;

interface NewsletterSubscriptionInterface
{
    public function getId(): ?int;

    public function getUuid(): UuidInterface;

    public function getEmail(): ?string;

    public function getToken(): ?UuidInterface;

    public function isConfirmed(): bool;
}
