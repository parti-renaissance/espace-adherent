<?php

namespace App\Validator\TerritorialCouncil;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueTerritorialCouncilMemberValidator extends ConstraintValidator
{
    private $adherentRepository;
    private $translator;

    public function __construct(AdherentRepository $adherentRepository, TranslatorInterface $translator)
    {
        $this->adherentRepository = $adherentRepository;
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueTerritorialCouncilMember) {
            throw new UnexpectedTypeException($constraint, UniqueTerritorialCouncilMember::class);
        }

        if (!\is_array($constraint->qualities)) {
            throw new UnexpectedTypeException($constraint->qualities, 'array');
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Adherent) {
            throw new UnexpectedTypeException($value, Adherent::class);
        }

        if (!$value->getTerritorialCouncilMembership()) {
            return;
        }

        foreach ($constraint->qualities as $quality) {
            if (!\in_array($quality, UniqueTerritorialCouncilMember::QUALITIES)) {
                throw new ConstraintDefinitionException(\sprintf('Territorial council quality "%s" can not be validated.', $quality));
            }

            if (!$value->getTerritorialCouncilMembership()->hasQuality($quality)) {
                continue;
            }

            $territorialCouncil = $value->getTerritorialCouncilMembership()->getTerritorialCouncil();
            /** @var Adherent $adherent */
            $adherent = $this->adherentRepository->findByTerritorialCouncilAndQuality($territorialCouncil, $quality, $value);

            if (null !== $adherent) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter(
                        '{{ adherent }}',
                        \sprintf(
                            '%s (%s)',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress())
                    )
                    ->setParameter(
                        '{{ quality }}',
                        $this->translator->trans(
                            UniqueTerritorialCouncilMember::QUALITIES_LABELS[$quality], [], 'forms'
                        )
                    )
                    ->setParameter('{{ territorialCouncil }}', $territorialCouncil)
                    ->addViolation()
                ;
            }
        }
    }
}
