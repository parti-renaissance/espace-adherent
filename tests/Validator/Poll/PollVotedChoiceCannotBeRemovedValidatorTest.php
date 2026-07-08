<?php

declare(strict_types=1);

namespace Tests\App\Validator\Poll;

use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Repository\Poll\ChoiceRepository;
use App\Repository\Poll\VoteRepository;
use App\Validator\Poll\PollVotedChoiceCannotBeRemoved;
use App\Validator\Poll\PollVotedChoiceCannotBeRemovedValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tests\App\Validator\ConstraintValidatorTestCase;

class PollVotedChoiceCannotBeRemovedValidatorTest extends ConstraintValidatorTestCase
{
    private ChoiceRepository|MockObject|null $choiceRepository = null;
    private VoteRepository|MockObject|null $voteRepository = null;

    protected function createValidator(): PollVotedChoiceCannotBeRemovedValidator
    {
        $this->choiceRepository = $this->createMock(ChoiceRepository::class);
        $this->voteRepository = $this->createMock(VoteRepository::class);

        return new PollVotedChoiceCannotBeRemovedValidator($this->choiceRepository, $this->voteRepository);
    }

    public function testNoRepositoryCallOnPollCreation(): void
    {
        $this->choiceRepository->expects($this->never())->method('findBy');
        $this->voteRepository->expects($this->never())->method('count');

        $this->validator->validate(new Poll(), new PollVotedChoiceCannotBeRemoved());

        $this->assertNoViolation();
    }

    public function testNoViolationWhenAllPersistedChoicesAreKept(): void
    {
        $choice = $this->choice(1, 'Thé');
        $poll = $this->poll(42, [$choice]);

        $this->choiceRepository->expects($this->once())->method('findBy')->willReturn([$choice]);
        $this->voteRepository->expects($this->never())->method('count');

        $this->validator->validate($poll, new PollVotedChoiceCannotBeRemoved());

        $this->assertNoViolation();
    }

    public function testNoViolationWhenRemovedChoiceHasNoVote(): void
    {
        $keptChoice = $this->choice(1, 'Thé');
        $removedChoice = $this->choice(2, 'Café');
        $poll = $this->poll(42, [$keptChoice]);

        $this->choiceRepository->expects($this->once())->method('findBy')->willReturn([$keptChoice, $removedChoice]);
        $this->voteRepository->expects($this->once())->method('count')->with(['choice' => $removedChoice])->willReturn(0);

        $this->validator->validate($poll, new PollVotedChoiceCannotBeRemoved());

        $this->assertNoViolation();
    }

    public function testViolationWhenRemovedChoiceHasVotes(): void
    {
        $keptChoice = $this->choice(1, 'Thé');
        $removedChoice = $this->choice(2, 'Café');
        $poll = $this->poll(42, [$keptChoice]);

        $this->choiceRepository->expects($this->once())->method('findBy')->willReturn([$keptChoice, $removedChoice]);
        $this->voteRepository->expects($this->once())->method('count')->willReturn(3);

        $constraint = new PollVotedChoiceCannotBeRemoved();
        $this->validator->validate($poll, $constraint);

        $this
            ->buildViolation($constraint->message)
            ->atPath('property.path.choices')
            ->setParameter('{{ choice }}', 'Café')
            ->assertRaised()
        ;
    }

    public function testUnexpectedValueType(): void
    {
        $this->choiceRepository->expects($this->never())->method('findBy');
        $this->voteRepository->expects($this->never())->method('count');

        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(new \stdClass(), new PollVotedChoiceCannotBeRemoved());
    }

    private function poll(int $id, array $choices): Poll
    {
        $poll = new Poll(null, 'Plutôt thé ou café ?', new \DateTimeImmutable('+1 day'), true);
        new \ReflectionProperty(Poll::class, 'id')->setValue($poll, $id);

        foreach ($choices as $choice) {
            $poll->addChoice($choice);
        }

        return $poll;
    }

    private function choice(int $id, string $value): Choice
    {
        $choice = new Choice($value);
        new \ReflectionProperty(Choice::class, 'id')->setValue($choice, $id);

        return $choice;
    }
}
