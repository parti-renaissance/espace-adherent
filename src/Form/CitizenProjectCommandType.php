<?php

namespace AppBundle\Form;

use AppBundle\CitizenProject\CitizenProjectCommand;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use AppBundle\Form\DataTransformer\CitizenProjectSkillTransformer;
use AppBundle\Form\DataTransformer\CommitteeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectCommandType extends AbstractType
{
    private $citizenProjectSkillTransformer;
    private $committeeTransformer;

    public function __construct(CitizenProjectSkillTransformer $citizenProjectSkillTransformer, CommitteeTransformer $committeeTransformer)
    {
        $this->citizenProjectSkillTransformer = $citizenProjectSkillTransformer;
        $this->committeeTransformer = $committeeTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('subtitle', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('category', EventCategoryType::class, [
                'class' => CitizenProjectCategory::class,
            ])
            ->add('problem_description', TextareaType::class, [
                'property_path' => 'problemDescription',
                'filter_emojis' => true,
            ])
            ->add('proposed_solution', TextareaType::class, [
                'property_path' => 'proposedSolution',
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('required_means', TextareaType::class, [
                'property_path' => 'requiredMeans',
                'filter_emojis' => true,
                'purify_html' => true,
            ])
            ->add('address', NullableAddressType::class)
            ->add('assistance_needed', CheckboxType::class, [
                'property_path' => 'assistanceNeeded',
                'required' => false,
            ])
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('assistance_content', TextareaType::class, [
                'required' => false,
                'property_path' => 'assistanceContent',
                'purify_html' => true,
                'filter_emojis' => true,
            ])
            ->add('skills', CollectionType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('skills_search', TextType::class, [
                'mapped' => false,
                'required' => false,
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'Ajouter des compétences',
                ],
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA, [$this, 'preSetData']
            );

        $builder->get('skills')->addModelTransformer($this->citizenProjectSkillTransformer);
        $builder->get('address')->remove('address');

        $citizenProject = $builder->getData()->getCitizenProject();

        if (!$citizenProject instanceof CitizenProject || !$citizenProject->isApproved()) {
            $builder
                ->add('committees', CollectionType::class, [
                    'required' => false,
                    'entry_type' => TextType::class,
                    'entry_options' => ['label' => false],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
                ->add('committees_search', TextType::class, [
                    'mapped' => false,
                    'required' => false,
                    'filter_emojis' => true,
                    'attr' => [
                        'placeholder' => 'Vous avez déjà le soutient d\'un comité local ? Indiquez son nom : (Optionnel)',
                    ],
                ])
            ;

            $builder->get('committees')->addModelTransformer($this->committeeTransformer);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'citizen_project';
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $citizenProject = $data->getCitizenProject();

        /** @var CitizenProject $citizenProject */
        if (null !== $citizenProject && $citizenProject->isApproved()) {
            $form->add('name', TextType::class, [
                'filter_emojis' => true,
                'disabled' => true,
            ]);
        }
    }
}
