<?php

namespace AppBundle\Validator\ChezVous;

use AppBundle\ChezVous\Measure\BaisseNombreChomeurs;
use AppBundle\ChezVous\Measure\CouvertureFibre;
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

        if (BaisseNombreChomeurs::getType() === $type) {
            if (
                !array_key_exists(BaisseNombreChomeurs::KEY_BAISSE_VILLE, $payload)
                && !array_key_exists(BaisseNombreChomeurs::KEY_BAISSE_DEPARTEMENT, $payload)
            ) {
                $this
                    ->context
                    ->buildViolation($constraint->defineAtLeastOneKeyForType)
                    ->atPath('type')
                    ->setParameters([
                        '{{ keys }}' => implode(', ', array_map(
                            function (string $key) use ($keyChoices) {
                                return $this->translateChoice(array_search($key, $keyChoices));
                            },
                            array_keys(BaisseNombreChomeurs::getKeys())
                        )),
                        '{{ type }}' => $this->translateChoice(array_search($type, $typeChoices)),
                    ])
                    ->addViolation()
                ;
            }
        }

        if (CouvertureFibre::getType() === $type) {
            $hausseVilleExists = array_key_exists(CouvertureFibre::KEY_HAUSSE_DEPUIS_2017_VILLE, $payload);
            $locauxVilleExists = array_key_exists(CouvertureFibre::KEY_NOMBRE_LOCAUX_RACCORDES_VILLE, $payload);

            if (($hausseVilleExists && !$locauxVilleExists) || (!$hausseVilleExists && $locauxVilleExists)) {
                $this
                    ->context
                    ->buildViolation($constraint->defineAtLeastOneKeyForType)
                    ->atPath('type')
                    ->setParameters([
                        '{{ keys }}' => implode(', ', array_map(
                            function (string $key) use ($keyChoices) {
                                return $this->translateChoice(array_search($key, $keyChoices));
                            },
                            [
                                CouvertureFibre::KEY_HAUSSE_DEPUIS_2017_VILLE,
                                CouvertureFibre::KEY_NOMBRE_LOCAUX_RACCORDES_VILLE,
                            ]
                        )),
                        '{{ type }}' => $this->translateChoice(array_search($type, $typeChoices)),
                    ])
                    ->addViolation()
                ;
            }

            $hausseDepartementExists = array_key_exists(CouvertureFibre::KEY_HAUSSE_DEPUIS_2017_DEPARTEMENT, $payload);
            $locauxDepartementExists = array_key_exists(CouvertureFibre::KEY_NOMBRE_LOCAUX_RACCORDES_DEPARTEMENT, $payload);

            if (
                ($hausseDepartementExists && !$locauxDepartementExists)
                || (!$hausseDepartementExists && $locauxDepartementExists)
            ) {
                $this
                    ->context
                    ->buildViolation($constraint->defineAtLeastOneKeyForType)
                    ->atPath('type')
                    ->setParameters([
                        '{{ keys }}' => implode(', ', array_map(
                            function (string $key) use ($keyChoices) {
                                return $this->translateChoice(array_search($key, $keyChoices));
                            },
                            [
                                CouvertureFibre::KEY_HAUSSE_DEPUIS_2017_DEPARTEMENT,
                                CouvertureFibre::KEY_NOMBRE_LOCAUX_RACCORDES_DEPARTEMENT,
                            ]
                        )),
                        '{{ type }}' => $this->translateChoice(array_search($type, $typeChoices)),
                    ])
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
