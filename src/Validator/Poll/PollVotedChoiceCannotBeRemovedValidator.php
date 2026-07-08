<?php

declare(strict_types=1);

namespace App\Validator\Poll;

use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Repository\Poll\ChoiceRepository;
use App\Repository\Poll\VoteRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PollVotedChoiceCannotBeRemovedValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ChoiceRepository $choiceRepository,
        private readonly VoteRepository $voteRepository,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PollVotedChoiceCannotBeRemoved) {
            throw new UnexpectedTypeException($constraint, PollVotedChoiceCannotBeRemoved::class);
        }

        if (!$value instanceof Poll) {
            throw new UnexpectedValueException($value, Poll::class);
        }

        if (null === $value->getId()) {
            return;
        }

        $keptChoiceIds = array_filter(array_map(
            static fn (Choice $choice): ?int => $choice->getId(),
            $value->getChoices()->toArray(),
        ));

        foreach ($this->choiceRepository->findBy(['poll' => $value]) as $persistedChoice) {
            if (\in_array($persistedChoice->getId(), $keptChoiceIds, true)) {
                continue;
            }

            if ($this->voteRepository->count(['choice' => $persistedChoice]) > 0) {
                $this
                    ->context
                    ->buildViolation($constraint->message)
                    ->atPath('choices')
                    ->setParameter('{{ choice }}', (string) $persistedChoice->getValue())
                    ->addViolation()
                ;
            }
        }
    }
}
