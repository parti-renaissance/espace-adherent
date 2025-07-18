<?php

namespace App\PublicId;

interface PublicIdGeneratorInterface
{
    public function generate(): string;
}
