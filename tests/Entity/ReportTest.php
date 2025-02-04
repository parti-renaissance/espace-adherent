<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Report\Report;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class ReportTest extends TestCase
{
    public static function provideSubjectClass(): iterable
    {
        yield [Committee::class];
        yield [Event::class];
    }

    #[DataProvider('provideSubjectClass')]
    public function testConstructor(string $subjectClass): void
    {
        $report = $this->createReport($subjectClass, ['other', 'illicit_content', 'commercial_content', 'intellectual_property'], 'One comment');

        $this->assertSame(['other', 'illicit_content', 'commercial_content', 'intellectual_property'], $report->getReasons());
        $this->assertSame('One comment', $report->getComment());
        $this->assertSame('unresolved', $report->getStatus());
        $this->assertNull($report->getId());
        $this->assertInstanceOf(UuidInterface::class, $report->getUuid());
        $this->assertFalse($report->isResolved(), 'Report should not be resolved.');
        $this->assertInstanceOf(Adherent::class, $report->getAuthor());
    }

    #[DataProvider('provideSubjectClass')]
    public function testItShouldContainAtLeastOneReason(string $subjectClass): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one reason must be provided');
        $this->createReport($subjectClass, []);
    }

    #[DataProvider('provideSubjectClass')]
    public function testItShouldValidateReasons(string $subjectClass): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Some reasons are not valid "toto", they are defined in App\Entity\Report\ReportReasonEnum::REASONS_LIST');
        $this->createReport($subjectClass, ['toto']);
    }

    #[DataProvider('provideSubjectClass')]
    public function testItShouldRequireOtherReasonWhenCommentIsProvided(string $subjectClass): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$comment is filed but App\Entity\Report\ReportReasonEnum::REASON_OTHER is not provided in $reasons');
        $this->createReport($subjectClass, ['illicit_content'], 'comment');
    }

    #[DataProvider('provideSubjectClass')]
    public function testItShouldNotBeResolvedTwice(string $subjectClass): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Report already resolved');
        $report = $this->createReport($subjectClass, ['illicit_content']);

        $report->resolve();
        $report->resolve();
    }

    #[DataProvider('provideSubjectClass')]
    public function testStatusCanBeSetToResolved(string $subjectClass): void
    {
        $report = $this->createReport($subjectClass, ['illicit_content']);

        $this->assertFalse($report->isResolved(), 'Report should not be resolved.');
        $this->assertNull($report->getResolvedAt());

        $report->resolve();

        $this->assertTrue($report->isResolved(), 'Report should be resolved.');
        $this->assertSame('resolved', $report->getStatus());
        $this->assertNotNull($report->getResolvedAt());
    }

    private function createReport(string $subjectClass, array $reasons, ?string $comment = null): Report
    {
        return new class($this->createMock($subjectClass), $this->createMock(Adherent::class), $reasons, $comment) extends Report {
            // CS needed for Style CI
        };
    }
}
