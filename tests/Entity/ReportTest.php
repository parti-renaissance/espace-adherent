<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Report;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class ReportTest extends TestCase
{
    public function testConstructor(): void
    {
        $report = $this->createReport(['other', 'en_marche_values', 'commercial_content', 'inappropriate'], 'One comment');

        self::assertSame(['other', 'en_marche_values', 'commercial_content', 'inappropriate'], $report->getReasons());
        self::assertSame('One comment', $report->getComment());
        self::assertSame('unresolved', $report->getStatus());
        self::assertNull($report->getId());
        self::assertInstanceOf(UuidInterface::class, $report->getUuid());
        self::assertFalse($report->isResolved());
        self::assertInstanceOf(Adherent::class, $report->getAuthor());
    }

    public function testItShouldContainAtLeastOneReason(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one reason must be provided');
        $this->createReport([]);
    }

    public function testItShouldValidateReasons(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('toto is not a valid reason, you must choose one from AppBundle\Entity\Report::REASONS_LIST');
        $this->createReport(['toto']);
    }

    public function testItShouldRequireACommentWhenOtherReasonIsProvided(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$comment is not filed while AppBundle\Entity\Report::REASON_OTHER is provided');
        $this->createReport(['other']);
    }

    public function testItShouldRequireOtherReasonWhenCommentIsProvided(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$comment is filed but AppBundle\Entity\Report::REASON_OTHER is not provided in $reasons');
        $this->createReport(['inappropriate'], 'comment');
    }

    public function testItShouldNotBeResolvedTwice(): void
    {
        $report = $this->createReport(['inappropriate']);
        $report->resolve();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Report already resolved');
        $report->resolve();
    }

    public function testStatusCanBeSetToResolved(): void
    {
        $report = $this->createReport(['inappropriate']);

        self::assertFalse($report->isResolved());
        self::assertNull($report->getResolvedAt());
        $report->resolve();
        self::assertTrue($report->isResolved());
        self::assertSame('resolved', $report->getStatus());
        self::assertNotNull($report->getResolvedAt());
    }

    private function createReport(array $reasons, string $comment = null): Report
    {
        return new class($this->createMock(Adherent::class), $reasons, $comment) extends Report {
            public function getSubject()
            {
                return 'Foo';
            }

            public function getSubjectType(): string
            {
                return 'Bar';
            }
        };
    }
}
