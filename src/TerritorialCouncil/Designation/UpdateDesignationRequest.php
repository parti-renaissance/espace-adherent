<?php

namespace App\TerritorialCouncil\Designation;

use App\Address\Address;
use App\Validator\TerritorialCouncil\TerritorialCouncilDesignation as AssertTerritorialCouncilDesignation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertTerritorialCouncilDesignation
 */
class UpdateDesignationRequest
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=App\TerritorialCouncil\Designation\DesignationVoteModeEnum::ALL)
     */
    private $voteMode = DesignationVoteModeEnum::VOTE_MODE_ONLINE;

    /**
     * @var Address|null
     *
     * @Assert\Valid
     */
    private $address;

    /**
     * @var string|null
     *
     * @Assert\Url
     */
    private $meetingUrl;

    /**
     * @var \DateTime|null
     *
     * @Assert\DateTime
     */
    private $meetingStartDate;

    /**
     * @var \DateTime|null
     *
     * @Assert\DateTime
     */
    private $meetingEndDate;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $voteStartDate;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $voteEndDate;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(max=2000)
     */
    private $description;

    /**
     * @var string|null
     *
     * @Assert\Length(max=2000)
     */
    private $questions;

    /**
     * @var bool
     */
    private $withPoll = false;

    /**
     * @var string|null
     */
    private $electionPollGender;

    /**
     * @var array|null
     *
     * @Assert\Count(max=5)
     */
    private $electionPollChoices;

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

    public function getQuestions(): ?string
    {
        return $this->questions;
    }

    public function setQuestions(?string $questions): void
    {
        $this->questions = $questions;
    }

    public function isMeetingMode(): bool
    {
        return DesignationVoteModeEnum::VOTE_MODE_MEETING === $this->voteMode;
    }

    public function isWithPoll(): bool
    {
        return $this->withPoll;
    }

    public function setWithPoll(bool $withPoll): void
    {
        $this->withPoll = $withPoll;
    }

    public function getElectionPollGender(): ?string
    {
        return $this->electionPollGender;
    }

    public function setElectionPollGender(?string $electionPollGender): void
    {
        $this->electionPollGender = $electionPollGender;
    }

    public function getElectionPollChoices(): ?array
    {
        return $this->electionPollChoices;
    }

    public function setElectionPollChoices(?array $electionPollChoices): void
    {
        $this->electionPollChoices = $electionPollChoices;
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
