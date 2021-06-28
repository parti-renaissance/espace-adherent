<?php

namespace App\Form\Admin\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DesignationNotificationType extends AbstractType implements DataTransformerInterface
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => Designation::NOTIFICATION_ALL,
            'multiple' => true,
        ]);
    }

    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        return array_filter(Designation::NOTIFICATION_ALL, static function (int $notificationBit) use ($value) {
            return $notificationBit & $value;
        });
    }

    public function reverseTransform($value)
    {
        if (\is_array($value)) {
            return array_sum($value);
        }

        return $value;
    }
}
