<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Event\EventCategory;
use App\Event\ListFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['required' => false])
            ->add('category', EventCategoryType::class, [
                'placeholder' => 'Tous les événements',
                'required' => false,
                'group_by' => function ($category) {
                    /** @var EventCategory $category */
                    return $category->getEventGroupCategory()?->getName();
                },
                'choice_label' => fn (EventCategory $value) => ucfirst($value->getName()),
            ])
        ;
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
