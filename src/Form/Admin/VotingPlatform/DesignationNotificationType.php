<?php

declare(strict_types=1);

namespace App\Form\Admin\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DesignationNotificationType extends AbstractType implements DataTransformerInterface
{
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => Designation::NOTIFICATION_ALL,
            'multiple' => true,
        ]);
    }

    public function transform($value): mixed
    {
        if (null === $value) {
            return null;
        }

        return array_filter(Designation::NOTIFICATION_ALL, static function (int $notificationBit) use ($value) {
            return $notificationBit & $value;
        });
    }

    public function reverseTransform($value): mixed
    {
        if (\is_array($value)) {
            return array_sum($value);
        }

        return $value;
    }
}
