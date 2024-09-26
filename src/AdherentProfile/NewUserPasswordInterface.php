<?php

namespace App\AdherentProfile;

interface NewUserPasswordInterface
{
    public function getNewPassword(): ?string;

    public function getNewPasswordConfirmation(): ?string;
}
