<?php

namespace App\Entity\Audience;

use App\Entity\Geo\Zone;

interface AudienceInterface
{
    public function getName(): ?string;

    public function getFirstName(): ?string;

    public function getLastName(): ?string;

    public function getGender(): ?string;

    public function getAgeMin(): ?int;

    public function getAgeMax(): ?int;

    public function getRegisteredSince(): ?\DateTime;

    public function getRegisteredUntil(): ?\DateTime;

    public function getZone(): ?Zone;

    public function isCommitteeMember(): ?bool;

    public function isCertified(): ?bool;

    public function hasEmailSubscription(): ?bool;

    public function hasSmsSubscription(): ?bool;
}
