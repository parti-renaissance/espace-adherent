<?php

namespace AppBundle\Entity\Report;

use Ramsey\Uuid\UuidInterface;

interface ReportableInterface
{
    public function getUuid(): UuidInterface;

    public function getReportType(): string;
}
