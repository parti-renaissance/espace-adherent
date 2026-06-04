<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror\Handler;

use App\JeMengage\Timeline\Mirror\Message\DeleteTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteTimelineFeedCommandHandler
{
    public function __construct(private readonly TimelineFeedWriter $writer)
    {
    }

    public function __invoke(DeleteTimelineFeedCommand $command): void
    {
        $this->writer->delete($command->getUuid());
    }
}
