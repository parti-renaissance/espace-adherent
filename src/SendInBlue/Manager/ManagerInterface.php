<?php

namespace App\SendInBlue\Manager;

use App\SendInBlue\ContactInterface;

interface ManagerInterface
{
    public function supports(ContactInterface $contact): bool;

    public function getListId(): int;

    public function getIdentifier(ContactInterface $contact): string;

    public function getAttributes(ContactInterface $contact): array;
}
