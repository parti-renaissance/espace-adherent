<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\CitizenAction;
use App\Entity\CitizenProject;
use App\Entity\Committee;
use App\Entity\Event;
use App\Entity\IdeasWorkshop\Idea;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\IdeasWorkshop\ThreadComment;
use App\Entity\Report\Report;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class ReportTest extends TestCase
{
    public function provideSubjectClass(): iterable
    {
        yield [CitizenAction::class];
        yield [CitizenProject::class];
        yield [Committee::class];
        yield [Event::class];
        yield [Idea::class];
        yield [Thread::class];
        yield [ThreadComment::class];
    }

    /**
     * @dataProvider provideSubjectClass
     */
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

    /**
     * @dataProvider provideSubjectClass
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage At least one reason must be provided
     */
    public function testItShouldContainAtLeastOneReason(string $subjectClass): void
    {
        $this->createReport($subjectClass, []);
    }

    /**
     * @dataProvider provideSubjectClass
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Some reasons are not valid "toto", they are defined in App\Entity\Report\ReportReasonEnum::REASONS_LIST
     */
    public function testItShouldValidateReasons(string $subjectClass): void
    {
        $this->createReport($subjectClass, ['toto']);
    }

    /**
     * @dataProvider provideSubjectClass
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $comment is filed but App\Entity\Report\ReportReasonEnum::REASON_OTHER is not provided in $reasons
     */
    public function testItShouldRequireOtherReasonWhenCommentIsProvided(string $subjectClass): void
    {
        $this->createReport($subjectClass, ['illicit_content'], 'comment');
    }

    /**
     * @dataProvider provideSubjectClass
     * @expectedException \LogicException
     * @expectedExceptionMessage Report already resolved
     */
    public function testItShouldNotBeResolvedTwice(string $subjectClass): void
    {
        $report = $this->createReport($subjectClass, ['illicit_content']);

        $report->resolve();
        $report->resolve();
    }

    /**
     * @dataProvider provideSubjectClass
     */
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

    private function createReport(string $subjectClass, array $reasons, string $comment = null): Report
    {
        return new class($this->createMock($subjectClass), $this->createMock(Adherent::class), $reasons, $comment) extends Report {
            // CS needed for Style CI
        };
    }
}
