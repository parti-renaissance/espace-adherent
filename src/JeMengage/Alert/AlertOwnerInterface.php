<?php

declare(strict_types=1);

namespace App\JeMengage\Alert;

interface AlertOwnerInterface
{
    public function getSortableAlertDate(): \DateTimeInterface;
}
