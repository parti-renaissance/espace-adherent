<?php

declare(strict_types=1);

namespace App\Validator\Poll;

use App\Entity\Poll\Poll;
use App\Repository\Poll\PollRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PollDatesDoNotOverlapValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PollRepository $pollRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
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

        $conflictingPoll = $this->pollRepository->findConflictingPublishedPoll($value);

        if (null === $conflictingPoll) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ poll }}', htmlspecialchars((string) $conflictingPoll->getQuestion(), \ENT_QUOTES))
            ->setParameter('{{ url }}', $this->urlGenerator->generate('admin_app_poll_poll_edit', ['id' => $conflictingPoll->getId()]))
            ->addViolation()
        ;
    }
}
