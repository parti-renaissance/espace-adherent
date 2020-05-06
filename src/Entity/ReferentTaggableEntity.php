<?php

namespace App\Entity;

interface ReferentTaggableEntity extends EntityPostAddressInterface
{
    public function addReferentTag(ReferentTag $referentTag): void;

    public function removeReferentTag(ReferentTag $referentTag): void;

    public function clearReferentTags(): void;
}
