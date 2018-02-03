<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\Report\Report;
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
    }

    /**
     * @dataProvider provideSubjectClass
     */
    public function testConstructor(string $subjectClass): void
    {
        $report = $this->createReport($subjectClass, ['other', 'en_marche_values', 'commercial_content', 'inappropriate'], 'One comment');

        $this->assertSame(['other', 'en_marche_values', 'commercial_content', 'inappropriate'], $report->getReasons());
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
     * @expectedExceptionMessage Some reasons are not valid "toto", they are defined in AppBundle\Entity\Report\Report::REASONS_LIST
     */
    public function testItShouldValidateReasons(string $subjectClass): void
    {
        $this->createReport($subjectClass, ['toto']);
    }

    /**
     * @dataProvider provideSubjectClass
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $comment is not filed while AppBundle\Entity\Report\Report::REASON_OTHER is provided
     */
    public function testItShouldRequireACommentWhenOtherReasonIsProvided(string $subjectClass): void
    {
        $this->createReport($subjectClass, ['other']);
    }

    /**
     * @dataProvider provideSubjectClass
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $comment is filed but AppBundle\Entity\Report\Report::REASON_OTHER is not provided in $reasons
     */
    public function testItShouldRequireOtherReasonWhenCommentIsProvided(string $subjectClass): void
    {
        $this->createReport($subjectClass, ['inappropriate'], 'comment');
    }

    /**
     * @dataProvider provideSubjectClass
     * @expectedException \LogicException
     * @expectedExceptionMessage Report already resolved
     */
    public function testItShouldNotBeResolvedTwice(string $subjectClass): void
    {
        $report = $this->createReport($subjectClass, ['inappropriate']);

        $report->resolve();
        $report->resolve();
    }

    /**
     * @dataProvider provideSubjectClass
     */
    public function testStatusCanBeSetToResolved(string $subjectClass): void
    {
        $report = $this->createReport($subjectClass, ['inappropriate']);

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
