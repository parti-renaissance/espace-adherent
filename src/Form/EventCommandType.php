<?php

namespace App\Form;

use App\Entity\Coalition\Coalition;
use App\Entity\Event\EventCategory;
use App\Entity\Event\EventGroupCategory;
use App\Event\EventCommand;
use App\Repository\Coalition\CoalitionRepository;
use App\Repository\EventCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventCommandType extends AbstractType
{
    public function getParent()
    {
        return BaseEventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                'class' => $options['event_category_class'],
                'choice_label' => 'name',
                'query_builder' => function (EventCategoryRepository $ecr) use ($options) {
                    return $ecr->createQueryForAllEnabledOrderedByName($options['event_group_category']);
                },
                'group_by' => function ($category) {
                    /** @var EventCategory $category */
                    return $category->getEventGroupCategory()->getName();
                },
            ])
        ;

        if ($options['coalition']) {
            $builder
                ->add('coalition', EntityType::class, [
                    'constraints' => [new NotBlank()],
                    'class' => Coalition::class,
                    'choice_label' => 'name',
                    'query_builder' => function (CoalitionRepository $cr) {
                        return $cr->findEnabled();
                    },
                ])
                ->remove('capacity')
                ->remove('image')
            ;
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['coalition'] = $options['coalition'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => EventCommand::class,
                'event_group_category' => null,
                'coalition' => false,
            ])
            ->setAllowedTypes('event_group_category', ['null', EventGroupCategory::class])
            ->setAllowedTypes('coalition', ['bool'])
        ;
    }
}
