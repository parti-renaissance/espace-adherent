<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait DynamicSegmentTrait
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $mailchimpId;

    /**
     * @var int|null
     */
    #[Groups(['audience_segment_read'])]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private $recipientCount;

    /**
     * @var bool
     */
    #[Groups(['audience_segment_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $synchronized = false;

    public function getMailchimpId(): ?int
    {
        return $this->mailchimpId;
    }

    public function setMailchimpId(int $mailchimpId): void
    {
        $this->mailchimpId = $mailchimpId;
    }

    public function getRecipientCount(): ?int
    {
        return $this->recipientCount;
    }

    public function setRecipientCount(?int $recipientCount): void
    {
        $this->recipientCount = $recipientCount;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function setSynchronized(bool $synchronized): void
    {
        $this->synchronized = $synchronized;
    }
}
