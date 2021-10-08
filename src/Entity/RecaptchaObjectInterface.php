<?php

namespace App\Entity;

interface RecaptchaObjectInterface
{
    public function getRecaptcha(): ?string;

    public function isRequiredRecaptcha(): bool;
}
