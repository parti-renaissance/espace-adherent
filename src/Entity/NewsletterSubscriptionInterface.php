<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Uid\Uuid;

interface NewsletterSubscriptionInterface
{
    public function getId(): ?int;

    public function getUuid(): Uuid;

    public function getEmail(): ?string;

    public function getToken(): ?Uuid;

    public function isConfirmed(): bool;
}
