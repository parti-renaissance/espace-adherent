<?php

declare(strict_types=1);

namespace App\Form\Poll;

use App\Entity\Geo\Zone;
use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\Poll;
use App\Form\DateTimePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Poll $poll */
        $poll = $builder->getData();
        $hasVote = $poll->hasVote();

        $builder
            ->add('question', TextType::class, [
                'disabled' => $hasVote,
            ])
            ->add('finishAt', DateTimePickerType::class)
            ->add('published', null, [
                'label' => 'Publié',
                'required' => false,
            ])
        ;

        if ($poll instanceof LocalPoll) {
            $builder
                ->add('zone', EntityType::class, [
                    'class' => Zone::class,
                    'choices' => $options['zones'],
                    'disabled' => $hasVote,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['zones'])
            ->setAllowedTypes('zones', [Zone::class.'[]'])
            ->setDefaults([
                'data_class' => Poll::class,
                'zones' => [],
            ])
        ;
    }
}
