<?php

namespace AppBundle\Form;

use AppBundle\Entity\PurchasingPowerChoice;
use AppBundle\Repository\PurchasingPowerChoiceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchasingPowerChoiceEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('step')
            ->setAllowedValues('step', array_keys(PurchasingPowerChoice::STEPS))
            ->setDefaults([
                'class' => PurchasingPowerChoice::class,
                'query_builder' => function (Options $options) {
                    $step = $options['step'];

                    return function (PurchasingPowerChoiceRepository $repository) use ($step) {
                        return $repository->createQueryBuilderForStep($step);
                    };
                },
                'choice_value' => 'id', // we need to override the default because pre set data may not be managed by doctrine
                'choice_label' => 'label',
                'label' => false,
            ])
        ;
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
