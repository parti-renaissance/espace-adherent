<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait StaticSegmentTrait
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mailchimpId;

    public function getMailchimpId(): ?int
    {
        return $this->mailchimpId;
    }

    public function setMailchimpId(int $mailchimpId): void
    {
        $this->mailchimpId = $mailchimpId;
    }
}
