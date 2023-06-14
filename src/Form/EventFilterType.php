<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Entity\Event\EventCategory;
use App\Entity\Event\EventGroupCategory;
use App\Entity\Geo\Zone;
use App\Event\ListFilter;
use App\Repository\EventCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class EventFilterType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Adherent $user */
        $user = $this->security->getUser();
        $builder
            ->add('name', TextType::class, ['required' => false])
            ->add('category', EntityType::class, [
                'class' => EventCategory::class,
                'placeholder' => 'Tous les événements',
                'required' => false,
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
        if (!$user->isForeignResident()) {
            $builder
                ->add('zone', ZoneAutoCompleteType::class, [
                    'required' => false,
                    'multiple' => false,
                    'remote_params' => [
                        'active_only' => true,
                        'types' => [Zone::CITY, Zone::BOROUGH],
                        'for_re' => true,
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ListFilter::class,
                'event_group_category' => null,
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
            ])
            ->setAllowedTypes('event_group_category', ['null', EventGroupCategory::class])
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
