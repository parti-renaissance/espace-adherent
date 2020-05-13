<?php

namespace App\Validator;

use App\Committee\CommitteeMergeCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class MergeableCommitteesValidator extends ConstraintValidator
{
    public function validate($committeeMergeCommand, Constraint $constraint)
    {
        if (!$committeeMergeCommand instanceof CommitteeMergeCommand) {
            return;
        }

        $sourceCommittee = $committeeMergeCommand->getSourceCommittee();
        $destinationCommittee = $committeeMergeCommand->getDestinationCommittee();

        if (!$sourceCommittee || !$destinationCommittee) {
            return;
        }

        if (!$sourceCommittee->isApproved()) {
            $this
                ->context
                ->buildViolation($constraint->notApprovedMessage)
                ->atPath('sourceCommittee')
                ->setParameter('{{ committee_name }}', $sourceCommittee->getName())
                ->setParameter('{{ committee_id }}', $sourceCommittee->getId())
                ->addViolation()
            ;
        }

        if (!$destinationCommittee->isApproved()) {
            $this
                ->context
                ->buildViolation($constraint->notApprovedMessage)
                ->atPath('destinationCommittee')
                ->setParameter('{{ committee_name }}', $destinationCommittee->getName())
                ->setParameter('{{ committee_id }}', $destinationCommittee->getId())
                ->addViolation()
            ;
        }

        if ($sourceCommittee->equals($destinationCommittee)) {
            $this
                ->context
                ->buildViolation($constraint->sameCommitteeMessage)
                ->addViolation()
            ;
        }
    }
}
