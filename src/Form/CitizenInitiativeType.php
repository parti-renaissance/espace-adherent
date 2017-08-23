<?php

namespace AppBundle\Form;

use AppBundle\CitizenInitiative\CitizenInitiativeCommand;
use AppBundle\Form\EventListener\SkillListener;
use AppBundle\Repository\SkillRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenInitiativeType extends AbstractType
{
    private $skillRepository;

    public function __construct(SkillRepository $skillRepository)
    {
        $this->skillRepository = $skillRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('category', CitizenInitiativeCategoryType::class)
            ->add('description', TextareaType::class, [
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('address', AddressType::class)
            ->add('beginAt', DateTimeType::class, [
                'years' => $options['years'],
                'minutes' => $options['minutes'],
            ])
            ->add('finishAt', DateTimeType::class, [
                'years' => $options['years'],
                'minutes' => $options['minutes'],
            ])
            ->add('interests', MemberInterestsChoiceType::class)
            ->add('expert_assistance_needed', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'placeholder' => false,
                'choices' => array(
                    'Oui' => true,
                    'Non' => false,
                ),
            ])
            ->add('expert_assistance_description', TextareaType::class, [
                'required' => false,
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('skill_search', TextType::class, [
                'mapped' => false,
                'required' => false,
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'Saisissez une compétence et tapez Entrée ou cliquez "Ajouter"',
                ],
            ])
            ->add('skills', CollectionType::class, [
                'required' => false,
                'entry_type' => SkillType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('coaching_requested', CheckboxType::class, [
                'required' => false,
            ])
            ->add('coaching_request', CoachingRequestType::class, [
                'required' => false,
            ])
            ->add('capacity', IntegerType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventSubscriber(new SkillListener($this->skillRepository));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $years = range((int) date('Y'), (int) date('Y') + 5);

        $resolver->setDefaults([
            'data_class' => CitizenInitiativeCommand::class,
            'years' => array_combine($years, $years),
            'minutes' => [
                '00' => '0',
                '15' => '15',
                '30' => '30',
                '45' => '45',
            ],
        ]);
    }
}
