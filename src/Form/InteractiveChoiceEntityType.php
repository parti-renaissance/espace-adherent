<?php

namespace AppBundle\Form;

use AppBundle\Entity\InteractiveChoice;
use AppBundle\Repository\InteractiveChoiceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InteractiveChoiceEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('step')
            ->setRequired('interactive')
            ->setAllowedValues('step', array_keys(InteractiveChoice::STEPS))
            ->setDefaults([
                'class' => InteractiveChoice::class,
                'query_builder' => function (Options $options) {
                    $params = [
                        'step' => $options['step'],
                        'interactive' => $options['interactive'],
                    ];

                    return function (InteractiveChoiceRepository $repository) use ($params) {
                        return $repository->createQueryBuilderForStep($params);
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
