<?php

namespace App\Validator;

use App\Entity\Biography\ExecutiveOfficeMember;
use App\Repository\Biography\ExecutiveOfficeMemberRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueExecutiveOfficeMemberRoleValidator extends ConstraintValidator
{
    /**
     * @var ExecutiveOfficeMemberRepository
     */
    private $executiveOfficeMemberRepository;

    public function __construct(ExecutiveOfficeMemberRepository $executiveOfficeMemberRepository)
    {
        $this->executiveOfficeMemberRepository = $executiveOfficeMemberRepository;
    }

    /**
     * @param ExecutiveOfficeMember $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueExecutiveOfficeMemberRole) {
            throw new UnexpectedTypeException($constraint, UniqueExecutiveOfficeMemberRole::class);
        }

        if (!$value instanceof ExecutiveOfficeMember) {
            throw new UnexpectedValueException($value, ExecutiveOfficeMember::class);
        }

        if ($value->isExecutiveOfficer()) {
            $executiveOfficer = $this->executiveOfficeMemberRepository->findOneExecutiveOfficerMember(true, $value->isForRenaissance());

            if ($executiveOfficer && $value->getUuid() !== $executiveOfficer->getUuid()) {
                $this->context
                    ->buildViolation($constraint->uniqueExecutiveOfficerMessage)
                    ->setParameter('{{ fullName }}', $executiveOfficer->getFullName())
                    ->atPath('role')
                    ->addViolation()
                ;

                return;
            }
        }

        if ($value->isPresident()) {
            $president = $this->executiveOfficeMemberRepository->findOnePresidentMember(true, $value->isForRenaissance());

            if ($president && $value->getUuid() !== $president->getUuid()) {
                $this->context
                    ->buildViolation($constraint->uniquePresidentMessage)
                    ->setParameter('{{ fullName }}', $president->getFullName())
                    ->atPath('role')
                    ->addViolation()
                ;

                return;
            }
        }
    }
}
