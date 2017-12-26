<?php

namespace AppBundle\Form;

use AppBundle\CitizenInitiative\CitizenInitiativeCommand;
use AppBundle\Entity\CitizenInitiativeCategory;
use AppBundle\Form\EventListener\SkillListener;
use AppBundle\Repository\SkillRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

    public function getParent()
    {
        return BaseEventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('place', TextType::class, [
                'filter_emojis' => true,
                'purify_html' => true,
                'required' => false,
            ])
            ->remove('capacity')
            ->addEventSubscriber(new SkillListener($this->skillRepository))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CitizenInitiativeCommand::class,
                'event_category_class' => CitizenInitiativeCategory::class,
            ])
        ;
    }
}
