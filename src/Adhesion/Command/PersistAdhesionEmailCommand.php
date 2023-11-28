<?php

namespace App\Adhesion\Command;

class PersistAdhesionEmailCommand
{
    public function __construct(public readonly string $email)
    {
    }
}
