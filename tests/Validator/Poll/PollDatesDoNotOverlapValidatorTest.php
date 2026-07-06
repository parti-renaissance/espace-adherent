<?php

declare(strict_types=1);

namespace Tests\App\Validator\Poll;

use App\Entity\Poll\Poll;
use App\Repository\Poll\PollRepository;
use App\Validator\Poll\PollDatesDoNotOverlap;
use App\Validator\Poll\PollDatesDoNotOverlapValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tests\App\Validator\ConstraintValidatorTestCase;

class PollDatesDoNotOverlapValidatorTest extends ConstraintValidatorTestCase
{
    private PollRepository|MockObject|null $pollRepository = null;

    protected function createValidator(): PollDatesDoNotOverlapValidator
    {
        $this->pollRepository = $this->createMock(PollRepository::class);

        return new PollDatesDoNotOverlapValidator($this->pollRepository);
    }

    public function testNoViolationWhenNoConflictingPoll(): void
    {
        $startAt = new \DateTimeImmutable('2026-07-10 10:00:00');
        $finishAt = new \DateTimeImmutable('2026-07-12 10:00:00');

        $this->pollRepository
            ->expects($this->once())
            ->method('countConflictingPolls')
            ->with(
                $this->equalTo($startAt->modify('-6 hours')),
                $this->equalTo($finishAt->modify('+6 hours')),
                null
            )
            ->willReturn(0)
        ;

        $this->validator->validate($this->poll($startAt, $finishAt), new PollDatesDoNotOverlap());

        $this->assertNoViolation();
    }

    public function testViolationWhenConflictingPollExists(): void
    {
        $this->pollRepository->expects($this->once())->method('countConflictingPolls')->willReturn(1);

        $constraint = new PollDatesDoNotOverlap();
        $this->validator->validate($this->poll(new \DateTimeImmutable('2026-07-10 10:00:00'), new \DateTimeImmutable('2026-07-12 10:00:00')), $constraint);

        $this
            ->buildViolation($constraint->message)
            ->atPath('property.path.startAt')
            ->setParameter('{{ hours }}', '6')
            ->assertRaised()
        ;
    }

    public function testExcludesItselfWhenEditingExistingPoll(): void
    {
        $startAt = new \DateTimeImmutable('2026-07-10 10:00:00');
        $finishAt = new \DateTimeImmutable('2026-07-12 10:00:00');

        $poll = $this->poll($startAt, $finishAt);
        $reflection = new \ReflectionProperty(Poll::class, 'id');
        $reflection->setValue($poll, 42);

        $this->pollRepository
            ->expects($this->once())
            ->method('countConflictingPolls')
            ->with($this->anything(), $this->anything(), 42)
            ->willReturn(0)
        ;

        $this->validator->validate($poll, new PollDatesDoNotOverlap());

        $this->assertNoViolation();
    }

    public function testNoRepositoryCallWhenDatesAreMissing(): void
    {
        $this->pollRepository->expects($this->never())->method('countConflictingPolls');

        $this->validator->validate($this->poll(null, new \DateTimeImmutable('2026-07-12 10:00:00')), new PollDatesDoNotOverlap());
        $this->validator->validate($this->poll(new \DateTimeImmutable('2026-07-10 10:00:00'), null), new PollDatesDoNotOverlap());

        $this->assertNoViolation();
    }

    public function testNoRepositoryCallWhenFinishAtIsBeforeStartAt(): void
    {
        $this->pollRepository->expects($this->never())->method('countConflictingPolls');

        $this->validator->validate($this->poll(new \DateTimeImmutable('2026-07-12 10:00:00'), new \DateTimeImmutable('2026-07-10 10:00:00')), new PollDatesDoNotOverlap());

        $this->assertNoViolation();
    }

    public function testUnexpectedValueType(): void
    {
        $this->pollRepository->expects($this->never())->method('countConflictingPolls');

        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(new \stdClass(), new PollDatesDoNotOverlap());
    }

    private function poll(?\DateTimeImmutable $startAt, ?\DateTimeImmutable $finishAt): Poll
    {
        $poll = new Poll(null, 'Question ?', $finishAt, true, $startAt);

        if (null === $startAt) {
            $poll->setStartAt(null);
        }

        return $poll;
    }
}
