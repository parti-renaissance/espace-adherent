<?php

namespace AppBundle\Validator;

use AppBundle\Entity\TurnkeyProject;
use AppBundle\Repository\TurnkeyProjectRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueTurnkeyProjectPinnedValidator extends ConstraintValidator
{
    /**
     * @var TurnkeyProjectRepository
     */
    private $turnkeyProjectRepository;

    public function __construct(TurnkeyProjectRepository $turnkeyProjectRepository)
    {
        $this->turnkeyProjectRepository = $turnkeyProjectRepository;
    }

    /**
     * @param TurnkeyProject $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueTurnkeyProjectPinned) {
            throw new UnexpectedTypeException($constraint, UniqueTurnkeyProjectPinned::class);
        }

        if (!$value instanceof TurnkeyProject) {
            throw new UnexpectedTypeException($value, TurnkeyProject::class);
        }

        if ($value->isPinned()) {
            $turnkeyProject = $this->turnkeyProjectRepository->findPinned($value->getId());

            if ($turnkeyProject) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ name }}', $turnkeyProject->getName())
                    ->atPath('isPinned')
                    ->addViolation()
                ;
            }
        }
    }
}
