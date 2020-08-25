<?php

namespace App\Validator\TerritorialCouncil;

use App\TerritorialCouncil\Designation\UpdateDesignationRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
            throw new UnexpectedTypeException($value, UpdateDesignationRequest::class);
        }

        $voteStartDate = $value->getVoteStartDate();
        $voteEndDate = $value->getVoteEndDate();

        if (!$voteStartDate || !$voteEndDate) {
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
                    ->buildViolation($constraint->messageAddressEmpty)
                    ->atPath('meetingUrl')
                    ->addViolation()
                ;
            }
        }

        $meetingStartDate = $value->getMeetingStartDate();
        $meetingEndDate = $value->getMeetingEndDate();

        if (!$meetingStartDate) {
            $this->context
                ->buildViolation($constraint->messageDateEmpty)
                ->atPath('meetingStartDate')
                ->addViolation()
            ;

            return;
        }

        if (!$meetingEndDate) {
            $this->context
                ->buildViolation($constraint->messageDateEmpty)
                ->atPath('meetingEndDate')
                ->addViolation()
            ;

            return;
        }

        if ($meetingStartDate < $voteStartDate || $meetingStartDate > $voteEndDate) {
            $this->context
                ->buildViolation($constraint->messageMeetingDateInvalid)
                ->atPath('meetingStartDate')
                ->addViolation()
            ;
        }

        if ($meetingEndDate < $voteStartDate || $meetingEndDate > $voteEndDate) {
            $this->context
                ->buildViolation($constraint->messageMeetingDateInvalid)
                ->atPath('meetingEndDate')
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

        if ($value->isWithPoll()) {
            if (empty(array_filter($value->getElectionPollChoices()))) {
                $this->context
                    ->buildViolation($constraint->messageElectionPollChoiceInvalid)
                    ->atPath('electionPollChoices')
                    ->addViolation()
                ;

                return;
            }

            foreach ($value->getElectionPollChoices() as $choice) {
                if ($choice < 0 || $choice > 100) {
                    $this->context
                        ->buildViolation($constraint->messageElectionPollChoiceInvalid)
                        ->atPath('electionPollChoices')
                        ->addViolation()
                    ;

                    return;
                }
            }
        }
    }
}
