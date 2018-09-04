<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Biography\ExecutiveOfficeMember;
use AppBundle\Repository\Biography\ExecutiveOfficeMemberRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueExecutiveOfficerValidator extends ConstraintValidator
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
        if (!$constraint instanceof UniqueExecutiveOfficer) {
            throw new UnexpectedTypeException($constraint, UniqueExecutiveOfficer::class);
        }

        if (!$value instanceof ExecutiveOfficeMember) {
            throw new UnexpectedTypeException($value, ExecutiveOfficeMember::class);
        }

        if ($value->isExecutiveOfficer()) {
            $executiveOfficer = $this->executiveOfficeMemberRepository->findOneExecutiveOfficerMember();

            if ($executiveOfficer && $value->getUuid() !== $executiveOfficer->getUuid()) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ fullName }}', $executiveOfficer->getFullName())
                    ->atPath('executiveOfficer')
                    ->addViolation()
                ;
            }
        }
    }
}
