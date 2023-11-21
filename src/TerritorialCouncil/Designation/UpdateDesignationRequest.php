<?php

namespace App\TerritorialCouncil\Designation;

use App\Address\Address;
use App\Validator\TerritorialCouncil\TerritorialCouncilDesignation as AssertTerritorialCouncilDesignation;
use App\Validator\WysiwygLength;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertTerritorialCouncilDesignation
 */
class UpdateDesignationRequest
{
    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Choice(choices: DesignationVoteModeEnum::ALL)]
    private $voteMode = DesignationVoteModeEnum::VOTE_MODE_ONLINE;

    /**
     * @var Address|null
     */
    #[Assert\Valid]
    private $address;

    /**
     * @var string|null
     */
    #[Assert\Url]
    private $meetingUrl;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    private $meetingStartDate;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    private $meetingEndDate;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    private $voteStartDate;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    private $voteEndDate;

    /**
     * @var string|null
     *
     * @WysiwygLength(max=2000)
     */
    #[Assert\NotBlank]
    private $description;

    public function getVoteMode(): ?string
    {
        return $this->voteMode;
    }

    public function setVoteMode(?string $voteMode): void
    {
        $this->voteMode = $voteMode;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }

    public function getMeetingStartDate(): ?\DateTime
    {
        return $this->meetingStartDate;
    }

    public function setMeetingStartDate(?\DateTime $meetingStartDate): void
    {
        $this->meetingStartDate = $meetingStartDate;
    }

    public function getMeetingEndDate(): ?\DateTime
    {
        return $this->meetingEndDate;
    }

    public function setMeetingEndDate(?\DateTime $meetingEndDate): void
    {
        $this->meetingEndDate = $meetingEndDate;
    }

    public function getVoteStartDate(): ?\DateTime
    {
        return $this->voteStartDate;
    }

    public function setVoteStartDate(?\DateTime $voteStartDate): void
    {
        $this->voteStartDate = $voteStartDate;
    }

    public function getVoteEndDate(): ?\DateTime
    {
        return $this->voteEndDate;
    }

    public function setVoteEndDate(?\DateTime $voteEndDate): void
    {
        $this->voteEndDate = $voteEndDate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isMeetingMode(): bool
    {
        return DesignationVoteModeEnum::VOTE_MODE_MEETING === $this->voteMode;
    }

    public function getMeetingUrl(): ?string
    {
        return $this->meetingUrl;
    }

    public function setMeetingUrl(?string $meetingUrl): void
    {
        $this->meetingUrl = $meetingUrl;
    }
}
