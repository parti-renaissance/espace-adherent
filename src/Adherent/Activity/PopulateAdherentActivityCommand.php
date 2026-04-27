<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

use App\Messenger\Message\CronjobMessageInterface;

readonly class PopulateAdherentActivityCommand implements CronjobMessageInterface
{
    public function __construct(
        public SourceTypeEnum $sourceType = SourceTypeEnum::ActionHistory,
    ) {
    }
}
