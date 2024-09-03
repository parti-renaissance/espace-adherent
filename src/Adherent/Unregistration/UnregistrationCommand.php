<?php

namespace App\Adherent\Unregistration;

use App\Entity\Administrator;
use Symfony\Component\Validator\Constraints as Assert;

class UnregistrationCommand
{
    #[Assert\NotBlank(message: 'adherent.unregistration.reasons')]
    private array $reasons;

    #[Assert\Length(max: 1000, groups: ['Default', 'admin'])]
    private ?string $comment;

    private ?Administrator $excludedBy;

    private bool $notification;

    public function __construct(
        array $reasons = [],
        ?string $comment = null,
        ?Administrator $excludedBy = null,
        bool $notification = false,
    ) {
        $this->reasons = $reasons;
        $this->comment = $comment;
        $this->excludedBy = $excludedBy;
        $this->notification = $notification;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getExcludedBy(): ?Administrator
    {
        return $this->excludedBy;
    }

    public function getNotification(): bool
    {
        return $this->notification;
    }

    public function setNotification(bool $notification): void
    {
        $this->notification = $notification;
    }

    public function setExcludedBy(?Administrator $admin): void
    {
        $this->excludedBy = $admin;
    }
}
