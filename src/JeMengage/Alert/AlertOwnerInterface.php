<?php

namespace App\JeMengage\Alert;

interface AlertOwnerInterface
{
    public function getSortableAlertDate(): \DateTimeInterface;
}
