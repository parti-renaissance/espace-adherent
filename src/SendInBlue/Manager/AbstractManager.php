<?php

namespace App\SendInBlue\Manager;

use App\Utils\PhoneNumberUtils;
use libphonenumber\PhoneNumber;

abstract class AbstractManager implements ManagerInterface
{
    private int $listId;

    public function __construct(int $listId)
    {
        $this->listId = $listId;
    }

    public function getListId(): int
    {
        return $this->listId;
    }

    protected static function formatDate(?\DateTimeInterface $date): ?string
    {
        return $date ? $date->format('Y-m-d') : null;
    }

    protected static function formatPhone(?PhoneNumber $phoneNumber): string
    {
        return PhoneNumberUtils::format($phoneNumber);
    }
}
