<?php

namespace App\Coalition;

use App\Entity\Coalition\Cause;

class CoalitionUrlGenerator
{
    private const CAUSE_LINK_PATTERN = '%s/cause/%s';
    private const CAUSE_LIST_LINK_PATTERN = '%s/causes';
    private const CREATE_ACCOUNT_LINK_PATTERN = '%s/inscription';

    private $coalitionsHost;

    public function __construct(string $coalitionsHost)
    {
        $this->coalitionsHost = $coalitionsHost;
    }

    public function generateCauseLink(Cause $cause): string
    {
        return sprintf(self::CAUSE_LINK_PATTERN, $this->coalitionsHost, $cause->getUuid()->toString());
    }

    public function generateCauseListLink(): string
    {
        return sprintf(self::CAUSE_LIST_LINK_PATTERN, $this->coalitionsHost);
    }

    public function generateCreateAccountLink(): string
    {
        return sprintf(self::CREATE_ACCOUNT_LINK_PATTERN, $this->coalitionsHost);
    }
}
