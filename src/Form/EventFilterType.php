<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Entity\Event\EventCategory;
use App\Entity\Geo\Zone;
use App\Event\ListFilter;
use App\Geo\ManagedZoneProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('category', EventCategoryType::class, [
                'placeholder' => 'Tous les événements',
                'required' => false,
                'group_by' => function ($category) {
                    /** @var EventCategory $category */
                    return $category->getEventGroupCategory()->getName();
                },
                'choice_label' => fn ($value) => ucfirst($value),
            ])
        ;
        if (!$user->isForeignResident()) {
            $builder
                ->add('zone', ZoneAutoCompleteType::class, [
                    'required' => false,
                    'multiple' => false,
                    'theme' => 'default renaissance-filters',
                    'autostart' => true,
                    'remote_params' => [
                        'active_only' => true,
                        'types' => [Zone::CITY, Zone::BOROUGH, Zone::DEPARTMENT, Zone::REGION, Zone::COUNTRY, Zone::CUSTOM],
                        'space_type' => ManagedZoneProvider::PUBLIC_SPACE,
                    ],
                    'data' => $builder->getData()->getDefaultZone(),
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ListFilter::class,
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
