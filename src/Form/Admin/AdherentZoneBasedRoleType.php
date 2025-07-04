<?php

namespace App\Form\Admin;

use App\Adherent\AdherentRoles;
use App\Admin\AdherentZoneBasedRoleAdmin;
use App\Entity\AdherentZoneBasedRole;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdherentZoneBasedRoleType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Rôle national ou local',
                'choices' => AdherentRoles::getZoneBasedRoles(),
                'choice_label' => function (string $label): string {
                    return $this->translator->trans("role.$label", ['gender' => 'male']);
                },
            ])
            ->add('zones', AdminZoneAutocompleteType::class, [
                'label' => 'Zones',
                'multiple' => true,
                'model_manager' => $options['model_manager'],
                'admin_code' => AdherentZoneBasedRoleAdmin::SERVICE_ID,
                'template' => 'admin/adherent/partial/zone_based_role_autocomplete.html.twig',
                'minimum_input_length' => 1,
            ])
            ->add('hidden', CheckboxType::class, [
                'label' => 'Rôle caché',
                'required' => false,
                'help' => "Les rôles cachés ne sont pas visibles sur l'espace militant et l'espace cadre et permettent de contourner la contrainte d'unicité rôle/zone attribuée.",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
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
