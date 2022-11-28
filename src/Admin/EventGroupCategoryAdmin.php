<?php

namespace App\Admin;

use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\EventGroupCategory;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EventGroupCategoryAdmin extends EventCategoryAdmin
{
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $group = $this->getSubject();

        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
                'disabled' => EventGroupCategory::CAMPAIGN_EVENTS === $group->getSlug(),
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Visible' => BaseEventCategory::ENABLED,
                    'Masqué' => BaseEventCategory::DISABLED,
                ],
            ])
        ;
    }
}
