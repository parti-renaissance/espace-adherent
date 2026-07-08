<?php

declare(strict_types=1);

namespace Tests\App\Validator\Poll;

use App\Entity\Poll\Poll;
use App\Repository\Poll\PollRepository;
use App\Validator\Poll\PollDatesDoNotOverlap;
use App\Validator\Poll\PollDatesDoNotOverlapValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tests\App\Validator\ConstraintValidatorTestCase;

class PollDatesDoNotOverlapValidatorTest extends ConstraintValidatorTestCase
{
    private PollRepository|MockObject|null $pollRepository = null;
    private UrlGeneratorInterface|MockObject|null $urlGenerator = null;

    protected function createValidator(): PollDatesDoNotOverlapValidator
    {
        $this->pollRepository = $this->createMock(PollRepository::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        return new PollDatesDoNotOverlapValidator($this->pollRepository, $this->urlGenerator);
    }

    public function testNoRepositoryCallWhenFinishBeforeStart(): void
    {
        $this->pollRepository->expects($this->never())->method('findConflictingPublishedPoll');
        $this->urlGenerator->expects($this->never())->method('generate');

        $this->validator->validate($this->poll(null, '+2 days', '+1 day'), new PollDatesDoNotOverlap());

        $this->assertNoViolation();
    }

    public function testNoViolationWhenNoConflictingPoll(): void
    {
        $this->pollRepository->expects($this->once())->method('findConflictingPublishedPoll')->willReturn(null);
        $this->urlGenerator->expects($this->never())->method('generate');

        $this->validator->validate($this->poll(null, '+1 day', '+2 days'), new PollDatesDoNotOverlap());

        $this->assertNoViolation();
    }

    public function testViolationWhenConflictingPublishedPollExists(): void
    {
        $conflictingPoll = $this->poll(7, '+1 day', '+2 days', 'Président 2027');

        $this->pollRepository->expects($this->once())->method('findConflictingPublishedPoll')->willReturn($conflictingPoll);
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('admin_app_poll_poll_edit', ['id' => 7])
            ->willReturn('/app/poll-poll/7/edit')
        ;

        $constraint = new PollDatesDoNotOverlap();
        $this->validator->validate($this->poll(null, '+1 day', '+2 days'), $constraint);

        $this
            ->buildViolation($constraint->message)
            ->setParameter('{{ poll }}', 'Président 2027')
            ->setParameter('{{ url }}', '/app/poll-poll/7/edit')
            ->assertRaised()
        ;
    }

    public function testUnexpectedValueType(): void
    {
        $this->pollRepository->expects($this->never())->method('findConflictingPublishedPoll');
        $this->urlGenerator->expects($this->never())->method('generate');

        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(new \stdClass(), new PollDatesDoNotOverlap());
    }

    private function poll(?int $id, string $startAt, string $finishAt, string $question = 'Sondage de test'): Poll
    {
        $poll = new Poll(null, $question, new \DateTimeImmutable($finishAt), true, new \DateTimeImmutable($startAt));

        if (null !== $id) {
            new \ReflectionProperty(Poll::class, 'id')->setValue($poll, $id);
        }

        return $poll;
    }
}
