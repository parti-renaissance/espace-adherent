<?php

namespace App\Form\MyTeam;

use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\DelegatedAccessEnum;
use App\Form\CommitteeUuidType;
use App\Form\DataTransformer\AdherentToEmailTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DelegateAccessType extends AbstractType
{
    /** @var AdherentToEmailTransformer */
    private $transformer;

    public function __construct(AdherentToEmailTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = DelegatedAccess::DEFAULT_ROLES;

        if ($builder->getData() && $builder->getData()->getRole()) {
            $roles[] = $builder->getData()->getRole();
        }

        $builder
            ->add('role', ChoiceType::class, [
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'select2'],
                'placeholder' => '',
                'choices' => \array_combine($roles, $roles),
                'choice_translation_domain' => false,
            ])
            ->add('delegated', HiddenType::class, [
                'invalid_message' => 'Aucun adhérent trouvé avec cette adresse email. Veuillez réessayer.',
            ])
            ->add('email', EmailType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Email',
            ])
            ->add('accesses', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices' => DelegatedAccessEnum::getAccessesForType($options['type']),
                'choice_label' => static function (string $choice) {
                    return "delegated_access.form.access.$choice";
                },
            ])
            ->add('restrictedCommittees_search', TextType::class, [
                'mapped' => false,
                'required' => false,
                'filter_emojis' => true,
            ])
            ->add('restrictedCommittees', CollectionType::class, [
                'required' => false,
                'entry_type' => CommitteeUuidType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
            ->add('restrictedCities_search', SearchType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('restrictedCities', CollectionType::class, [
                'required' => true,
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
        ;

        $builder->get('delegated')->addModelTransformer($this->transformer);

        // allow user to add custom values for roles
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $form = $event->getForm();

            $roles = DelegatedAccess::DEFAULT_ROLES;

            if ($role = ($event->getData()['role'] ?? null)) {
                $roles[] = $role;
            }

            $form->remove('role');
            $form->add('role', ChoiceType::class, [
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'select2'],
                'placeholder' => '',
                'choices' => \array_combine($roles, $roles),
                'choice_translation_domain' => false,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('type');
        $resolver->setRequired('type');
        $resolver->setAllowedTypes('type', 'string');
        $resolver->setAllowedValues('type', DelegatedAccessEnum::TYPES);

        $resolver->setDefaults([
            'data_class' => DelegatedAccess::class,
        ]);
    }
}
