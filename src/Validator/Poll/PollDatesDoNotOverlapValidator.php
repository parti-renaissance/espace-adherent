<?php

declare(strict_types=1);

namespace App\Validator\Poll;

use App\Entity\Poll\Poll;
use App\Repository\Poll\PollRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PollDatesDoNotOverlapValidator extends ConstraintValidator
{
    public function __construct(private readonly PollRepository $pollRepository)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PollDatesDoNotOverlap) {
            throw new UnexpectedTypeException($constraint, PollDatesDoNotOverlap::class);
        }

        if (!$value instanceof Poll) {
            throw new UnexpectedValueException($value, Poll::class);
        }

        $startAt = $value->getStartAt();
        $finishAt = $value->getFinishAt();

        if (null === $startAt || null === $finishAt || $finishAt <= $startAt) {
            return;
        }

        $lowerBound = $startAt->modify(\sprintf('-%d hours', PollDatesDoNotOverlap::MIN_GAP_HOURS));
        $upperBound = $finishAt->modify(\sprintf('+%d hours', PollDatesDoNotOverlap::MIN_GAP_HOURS));

        if ($this->pollRepository->countConflictingPolls($lowerBound, $upperBound, $value->getId()) > 0) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('startAt')
                ->setParameter('{{ hours }}', (string) PollDatesDoNotOverlap::MIN_GAP_HOURS)
                ->addViolation()
            ;
        }
    }
}
