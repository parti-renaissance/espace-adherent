<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class BirthdateType extends AbstractType
{
    public function getParent(): string
    {
        return BirthdayType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
                ->setDefaults([
                    'min_age' => 15,
                    'max_age' => 120,
                    'widget' => 'choice',
                    'reference_date' => new \DateTime(),
                    'years' => null, // reset initial value
                    'placeholder' => [
                        'year' => 'AAAA',
                        'month' => 'MM',
                        'day' => 'JJ',
                    ],
                ])
            ->setAllowedTypes('years', ['null', 'array'])
        ;

        $resolver->setAllowedTypes('min_age', 'int');
        $resolver->setAllowedTypes('max_age', 'int');
        $resolver->setAllowedTypes('reference_date', \DateTimeInterface::class);

        $resolver->setNormalizer('years', function (Options $options, $value) {
            if ($value) {
                return $value;
            }

            $referenceYear = (int) $options['reference_date']->format('Y');

            return range(
                $referenceYear - $options['min_age'],
                $referenceYear - $options['max_age']
            );
        });

        $resolver->setNormalizer('constraints', function (Options $options, $constraints) {
            $constraints ??= [];

            if (!\is_array($constraints)) {
                $constraints = [$constraints];
            }

            /** @var \DateTimeInterface $refDate */
            $refDate = $options['reference_date'];

            if ($options['min_age'] > 0) {
                $maxBirthDate = (clone $refDate)->modify('-'.$options['min_age'].' years');

                $constraints[] = new LessThanOrEqual(value: $maxBirthDate, message: \sprintf(
                    'Vous devez avoir %d ans',
                    $options['min_age']
                ));
            }

            if ($options['max_age'] > 0) {
                $minBirthDate = (clone $refDate)->modify('-'.$options['max_age'].' years');

                $constraints[] = new GreaterThanOrEqual(value: $minBirthDate, message: \sprintf(
                    'L\'âge maximum est de %d ans.',
                    $options['max_age']
                ));
            }

            return $constraints;
        });
    }
}
