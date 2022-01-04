<?php

namespace App\Form\Admin;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Admin\AdherentZoneBasedRoleAdmin;
use App\Entity\AdherentZoneBasedRole;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentZoneBasedRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => array_combine(ZoneBasedRoleTypeEnum::ALL, ZoneBasedRoleTypeEnum::ALL),
                'choice_label' => function (string $label): string {
                    return 'adherent.zone_based_role_type.'.$label;
                },
            ])
            ->add('zones', AdminZoneAutocompleteType::class, [
                'label' => 'Zones',
                'multiple' => true,
                'model_manager' => $options['model_manager'],
                'admin_code' => AdherentZoneBasedRoleAdmin::SERVICE_ID,
                'template' => 'admin/adherent/partial/zone_based_role_autocomplete.html.twig',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => AdherentZoneBasedRole::class,
            ])
            ->setRequired('model_manager')
            ->addAllowedTypes('model_manager', [ModelManagerInterface::class])
        ;
    }
}
