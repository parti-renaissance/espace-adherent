<?php

namespace AppBundle\Form;

use AppBundle\CitizenProject\CitizenProjectCommand;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCategory;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use AppBundle\Form\DataTransformer\CitizenProjectSkillTransformer;
use AppBundle\Form\DataTransformer\CommitteeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
                'with_character_count' => true,
                'attr' => ['maxlength' => 30],
            ])
            ->add('subtitle', TextType::class, [
                'filter_emojis' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 60],
            ])
            ->add('category', EventCategoryType::class, [
                'class' => CitizenProjectCategory::class,
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('problem_description', TextareaType::class, [
                'property_path' => 'problemDescription',
                'filter_emojis' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
            ])
            ->add('proposed_solution', TextareaType::class, [
                'property_path' => 'proposedSolution',
                'filter_emojis' => true,
                'purify_html' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 800],
            ])
            ->add('required_means', TextareaType::class, [
                'property_path' => 'requiredMeans',
                'filter_emojis' => true,
                'purify_html' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
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
            ->add('cgu', CheckboxType::class, [
                'mapped' => false,
            ])
            ->add('data_processing', CheckboxType::class, [
                'mapped' => false,
            ])
        ;

        $builder->get('skills')->addModelTransformer($this->citizenProjectSkillTransformer);
        $builder->get('address')->remove('address');

        $command = $builder->getData();

        if (!$command instanceof CitizenProjectCommand) {
            throw new InvalidConfigurationException('A pre set data is required in '.__CLASS__);
        }

        $citizenProject = $command->getCitizenProject();

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
                ]);

            $builder->get('committees')->addModelTransformer($this->committeeTransformer);
        }

        if ($command instanceof CitizenProjectCommand && $command->isCitizenProjectApproved()) {
            $builder->get('name')->setDisabled(true);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => CitizenProjectCommand::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'citizen_project';
    }
}
