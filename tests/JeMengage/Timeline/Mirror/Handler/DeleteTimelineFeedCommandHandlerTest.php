<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror\Handler;

use App\JeMengage\Timeline\Mirror\Handler\DeleteTimelineFeedCommandHandler;
use App\JeMengage\Timeline\Mirror\Message\DeleteTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

class DeleteTimelineFeedCommandHandlerTest extends TestCase
{
    public function testDeletesByObjectId(): void
    {
        $uuid = Uuid::v4();

        $writer = $this->createMock(TimelineFeedWriter::class);
        $writer->expects(self::once())->method('delete')->with($uuid);

        (new DeleteTimelineFeedCommandHandler($writer, new NullLogger()))(new DeleteTimelineFeedCommand($uuid));
    }
}
