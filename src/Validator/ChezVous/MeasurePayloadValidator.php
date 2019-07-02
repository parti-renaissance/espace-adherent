<?php

namespace AppBundle\Validator\ChezVous;

use AppBundle\ChezVous\MeasureChoiceLoader;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MeasurePayloadValidator extends ConstraintValidator
{
    private $measureChoiceLoader;
    private $translator;

    public function __construct(MeasureChoiceLoader $measureChoiceLoader, TranslatorInterface $translator)
    {
        $this->measureChoiceLoader = $measureChoiceLoader;
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MeasurePayload) {
            throw new UnexpectedTypeException($constraint, MeasurePayload::class);
        }

        $type = $value->getType();
        $payload = $value->getPayload();

        $typeChoices = $this->measureChoiceLoader->getTypeChoices();
        $keyChoices = $this->measureChoiceLoader->getKeyChoices();
        $expectedPayloadKeys = $this->measureChoiceLoader->getTypeKeysMap()[$type] ?? [];

        foreach ($payload as $key => $value) {
            if (!array_key_exists($key, $expectedPayloadKeys)) {
                $this
                    ->context
                    ->buildViolation($constraint->unexpectedKeyForType)
                    ->setParameters([
                        '{{ key }}' => $this->translateChoice(array_search($key, $keyChoices)),
                        '{{ type }}' => $this->translateChoice(array_search($type, $typeChoices)),
                    ])
                    ->atPath('type')
                    ->addViolation()
                ;
            }
        }

        foreach ($expectedPayloadKeys as $key => $required) {
            if ($required && !array_key_exists($key, $payload)) {
                $this
                    ->context
                    ->buildViolation($constraint->missingKeyForType)
                    ->setParameters([
                        '{{ key }}' => $this->translateChoice(array_search($key, $keyChoices)),
                        '{{ type }}' => $this->translateChoice(array_search($type, $typeChoices)),
                    ])
                    ->atPath('type')
                    ->addViolation()
                ;
            }
        }
    }

    private function translateChoice(string $choice): string
    {
        return $this->translator->trans($choice, [], 'forms');
    }
}
