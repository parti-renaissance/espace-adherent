<?php

namespace App\Form\Procuration;

use App\Entity\ElectionRound;
use App\Procuration\ElectionContext;
use App\Repository\ElectionRoundRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectionRoundsChoiceType extends AbstractType
{
    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => ElectionRound::class,
                'query_builder' => function (Options $options) {
                    $electionContext = $options['election_context'];

                    return function (ElectionRoundRepository $repository) use ($electionContext) {
                        return $repository->createQueryBuilderFromElectionContext($electionContext);
                    };
                },
                'expanded' => true,
                'multiple' => true,
                'choice_value' => 'id', // initial data may come from the session
                'choice_label' => 'label',
            ])
            ->setRequired('election_context')
            ->setAllowedTypes('election_context', ElectionContext::class)
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_procuration_election_rounds';
    }
}
