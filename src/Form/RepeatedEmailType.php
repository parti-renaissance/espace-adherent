<?php

declare(strict_types=1);

namespace App\Form;

use App\Validator\Repeated;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\ValueToDuplicatesTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepeatedEmailType extends AbstractType
{
    public function getParent(): string
    {
        return RepeatedType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['disable_duplicate'] ?? false) {
            $builder
                ->resetViewTransformers()
                ->addViewTransformer(new class([$options['first_name'], $options['second_name']]) extends ValueToDuplicatesTransformer {
                    private string $secondName;

                    public function __construct(array $keys)
                    {
                        parent::__construct($keys);
                        $this->secondName = end($keys);
                    }

                    public function transform($value): array
                    {
                        $result = parent::transform($value);
                        $result[$this->secondName] = null;

                        return $result;
                    }
                })
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('disable_duplicate')
            ->setAllowedTypes('disable_duplicate', 'bool')
            ->setDefaults([
                'type' => EmailType::class,
                'invalid_message' => 'common.email.repeated',
                'options' => [
                    'constraints' => [
                        new Repeated([
                            'message' => 'common.email.repeated',
                            'groups' => ['Registration', 'Update'],
                        ]),
                    ],
                ],
            ])
        ;
    }
}
