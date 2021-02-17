<?php

namespace App\Validator;

use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\VotingPlatform\Designation\CreatePartialDesignationCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CommitteePartialDesignationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CommitteePartialDesignation) {
            throw new UnexpectedTypeException($constraint, CommitteePartialDesignation::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof CreatePartialDesignationCommand) {
            throw new UnexpectedValueException($value, CreatePartialDesignationCommand::class);
        }

        $committee = $value->getCommittee();

        if ($committee->getCurrentDesignation() && $committee->getCurrentDesignation()->isOngoing()) {
            $this->context
                ->buildViolation($constraint->errorCommitteeAlreadyHasActiveDesignation)
                ->addViolation()
            ;

            return;
        }

        $designationType = $value->getDesignationType();

        $mandates = [];

        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $designationType) {
            $mandates = $committee->getActiveAdherentMandates()->toArray();
        } elseif (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designationType) {
            $mandates = $committee->findSupervisorMandates(null, false)->toArray();
        }

        $genders = array_unique(array_map(function (CommitteeAdherentMandate $mandate) {
            return $mandate->getGender();
        }, $mandates));

        if (\count($genders) >= 2) {
            $this->context
                ->buildViolation($constraint->errorDesignationTypeMessage)
                ->addViolation()
            ;

            return;
        }

        if (1 === \count($genders) && (!$value->getPool() || \in_array($value->getPool(), $genders, true))) {
            $this->context
                ->buildViolation($constraint->errorPoolMessage)
                ->addViolation()
            ;

            return;
        }
    }
}
