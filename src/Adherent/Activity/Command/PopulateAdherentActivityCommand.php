<?php

declare(strict_types=1);

namespace App\Adherent\Activity\Command;

use App\Adherent\Activity\SourceTypeEnum;
use App\Messenger\Message\CronjobMessageInterface;

readonly class PopulateAdherentActivityCommand implements CronjobMessageInterface
{
    public function __construct(
        public SourceTypeEnum $sourceType = SourceTypeEnum::ActionHistory,
        public int $lastId = 0,
    ) {
    }
}
