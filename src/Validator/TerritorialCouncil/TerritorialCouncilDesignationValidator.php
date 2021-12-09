<?php

namespace App\Validator\TerritorialCouncil;

use App\TerritorialCouncil\Designation\UpdateDesignationRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TerritorialCouncilDesignationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TerritorialCouncilDesignation) {
            throw new UnexpectedTypeException($constraint, TerritorialCouncilDesignation::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof UpdateDesignationRequest) {
            throw new UnexpectedValueException($value, UpdateDesignationRequest::class);
        }

        if ($value->isMeetingMode()) {
            if (!$value->getAddress()) {
                $this->context
                    ->buildViolation($constraint->messageAddressEmpty)
                    ->atPath('address')
                    ->addViolation()
                ;
            }
        } else {
            if (!$value->getMeetingUrl()) {
                $this->context
                    ->buildViolation($constraint->messageUrlEmpty)
                    ->atPath('meetingUrl')
                    ->addViolation()
                ;
            }
        }

        $voteStartDate = $value->getVoteStartDate();
        $voteEndDate = $value->getVoteEndDate();
        $meetingStartDate = $value->getMeetingStartDate();
        $meetingEndDate = $value->getMeetingEndDate();

        if (!$voteStartDate || !$voteEndDate || !$meetingStartDate || !$meetingEndDate) {
            return;
        }

        $now = new \DateTimeImmutable();

        if ($voteStartDate < $now->modify('+7 days')) {
            $this->context
                ->buildViolation($constraint->messageVoteStartDateTooClose)
                ->atPath('voteStartDate')
                ->addViolation()
            ;
        }

        if ($voteEndDate < (clone $voteStartDate)->modify('+5 hours') || $voteEndDate > (clone $voteStartDate)->modify('+7 days')) {
            $this->context
                ->buildViolation($constraint->messageVoteEndDateInvalid)
                ->atPath('voteEndDate')
                ->addViolation()
            ;
        }

        if ($meetingStartDate->format('d/m/Y') !== $voteStartDate->format('d/m/Y')) {
            $this->context
                ->buildViolation($constraint->messageVoteStartDateInvalid)
                ->atPath('voteStartDate')
                ->addViolation()
            ;
        }

        if ($meetingStartDate >= $meetingEndDate || $meetingEndDate > (clone $meetingStartDate)->modify('+12 hours')) {
            $this->context
                ->buildViolation($constraint->messageMeetingEndDateInvalid)
                ->atPath('meetingEndDate')
                ->addViolation()
            ;
        }

        if ($meetingStartDate > ($date = new \DateTime('+3 months'))) {
            $this->context
                ->buildViolation($constraint->messageMeetingStartDateTooFarAway)
                ->atPath('meetingStartDate')
                ->setParameter('{{date}}', $date->format('d/m/Y'))
                ->addViolation()
            ;
        }
    }
}
