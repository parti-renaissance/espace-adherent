<?php

namespace App\Entity\Audience;

use App\Entity\ZoneableWithScopeEntity;

interface AudienceInterface extends ZoneableWithScopeEntity
{
    public function getFirstName(): ?string;

    public function getLastName(): ?string;

    public function getGender(): ?string;

    public function getAgeMin(): ?int;

    public function getAgeMax(): ?int;

    public function getRegisteredSince(): ?\DateTime;

    public function getRegisteredUntil(): ?\DateTime;

    public function getIsCertified(): ?bool;

    public function getIsCommitteeMember(): ?bool;

    public function getHasSmsSubscription(): ?bool;

    public function getHasEmailSubscription(): ?bool;
}
