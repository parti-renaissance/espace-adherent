<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\SegmentFilterInterface;

interface SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool;

    public function buildFromFilter(SegmentFilterInterface $filter): array;
}
