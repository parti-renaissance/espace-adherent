<?php

namespace App\Adherent\Unregistration;

use App\Entity\Administrator;
use Symfony\Component\Validator\Constraints as Assert;

class UnregistrationCommand
{
    /**
     * @Assert\NotBlank(message="adherent.unregistration.reasons")
     */
    private $reasons = [];

    /**
     * @Assert\Length(min=10, max=1000, groups="Reason")
     * @Assert\NotBlank(groups="Reason")
     */
    private $comment;

    private $excludedBy;

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

    public function setExcludedBy(?Administrator $admin): void
    {
        $this->excludedBy = $admin;
    }
}
