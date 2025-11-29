<?php

declare(strict_types=1);

namespace App\Validator;

use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\DTO\CommitteeAdherentMandateCommand;
use App\Committee\Exception\CommitteeAdherentMandateException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AdherentForCommitteeMandateReplacementValidator extends ConstraintValidator
{
    private $mandateManager;

    public function __construct(CommitteeAdherentMandateManager $mandateManager)
    {
        $this->mandateManager = $mandateManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AdherentForCommitteeMandateReplacement) {
            throw new UnexpectedTypeException($constraint, AdherentForCommitteeMandateReplacement::class);
        }

        if (!$value) {
            return;
        }

        if (!$value instanceof CommitteeAdherentMandateCommand) {
            throw new UnexpectedValueException($value, CommitteeAdherentMandateCommand::class);
        }

        if ($value->getAdherent()) {
            try {
                $this->mandateManager->checkAdherentForMandateReplacement($value->getAdherent(), $value->getGender());
            } catch (CommitteeAdherentMandateException $ex) {
                $this
                    ->context
                    ->buildViolation($ex->getMessage())
                    ->atPath($constraint->errorPath)
                    ->addViolation()
                ;
            }
        }
    }
}
