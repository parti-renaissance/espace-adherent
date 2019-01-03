<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityThreadCommentStatusTrait
{
    /**
     * @Assert\Choice(choices=ThreadCommentStatusEnum::ALL_STATUSES, strict=true)
     *
     * @ORM\Column(length=9, options={"default": ThreadCommentStatusEnum::POSTED})
     *
     * @SymfonySerializer\Groups("comment_read")
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context": {
     *             "type": "string",
     *             "enum": ThreadCommentStatusEnum::ALL_STATUSES,
     *             "example": ThreadCommentStatusEnum::POSTED
     *         }
     *     }
     * )
     */
    protected $status = ThreadCommentStatusEnum::POSTED;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isPosted(): bool
    {
        return ThreadCommentStatusEnum::POSTED === $this->status;
    }

    public function isApproved(): bool
    {
        return ThreadCommentStatusEnum::APPROVED === $this->status;
    }

    public function isReported(): bool
    {
        return ThreadCommentStatusEnum::REPORTED === $this->status;
    }

    public function isRefused(): bool
    {
        return ThreadCommentStatusEnum::REFUSED === $this->status;
    }

    public function approve(): void
    {
        $this->status = ThreadCommentStatusEnum::APPROVED;
    }

    public function report(): void
    {
        $this->status = ThreadCommentStatusEnum::REPORTED;
    }
}
