<?php

namespace App\Validator;

use App\Entity\Biography\ExecutiveOfficeMember;
use App\Repository\Biography\ExecutiveOfficeMemberRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
            throw new UnexpectedTypeException($value, ExecutiveOfficeMember::class);
        }

        if ($value->isExecutiveOfficer()) {
            $executiveOfficer = $this->executiveOfficeMemberRepository->findOneExecutiveOfficerMember();

            if ($executiveOfficer && $value->getUuid() !== $executiveOfficer->getUuid()) {
                $this->context
                    ->buildViolation($constraint->uniqueExecutiveOfficerMessage)
                    ->setParameter('{{ fullName }}', $executiveOfficer->getFullName())
                    ->atPath('executiveOfficer')
                    ->addViolation()
                ;
            }
        }

        if ($value->isDeputyGeneralDelegate()) {
            $deputyGeneralDelegate = $this->executiveOfficeMemberRepository->findOneDeputyGeneralDelegateMember();

            if ($deputyGeneralDelegate && $value->getUuid() !== $deputyGeneralDelegate->getUuid()) {
                $this->context
                    ->buildViolation($constraint->uniqueDeputyGeneralDelegateMessage)
                    ->setParameter('{{ fullName }}', $deputyGeneralDelegate->getFullName())
                    ->atPath('deputyGeneralDelegate')
                    ->addViolation()
                ;
            }
        }

        if ($value->isExecutiveOfficer() && $value->isDeputyGeneralDelegate()) {
            $this->context
                ->buildViolation($constraint->uniqueRoleMessage)
                ->atPath('executiveOfficer')
                ->addViolation()
            ;
        }
    }
}
