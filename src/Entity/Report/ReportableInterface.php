<?php

declare(strict_types=1);

namespace App\Entity\Report;

use Symfony\Component\Uid\Uuid;

interface ReportableInterface
{
    public function getUuid(): Uuid;

    public function getReportType(): string;
}
