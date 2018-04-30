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
        $command = $builder->getData();

        if (!$command instanceof CitizenProjectCommand) {
            throw new InvalidConfigurationException('A pre set data is required in '.__CLASS__);
        }

        $citizenProject = $command->getCitizenProject();

        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
                'format_title_case' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 30],
                'disabled' => $command->isCitizenProjectApproved(),
            ])
            ->add('subtitle', TextType::class, [
                'filter_emojis' => true,
                'format_title_case' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 80],
            ])
            ->add('category', EventCategoryType::class, [
                'placeholder' => 'Choisir un thème',
                'class' => CitizenProjectCategory::class,
            ])
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('problem_description', TextareaType::class, [
                'property_path' => 'problemDescription',
                'filter_emojis' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
            ])
            ->add('proposed_solution', PurifiedTextareaType::class, [
                'property_path' => 'proposedSolution',
                'filter_emojis' => true,
                'purifier_type' => 'enrich_content',
                'with_character_count' => true,
                'attr' => ['maxlength' => 800],
            ])
            ->add('required_means', TextareaType::class, [
                'property_path' => 'requiredMeans',
                'filter_emojis' => true,
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
                'filter_emojis' => true,
                'attr' => ['maxlength' => 300],
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
                    'placeholder' => 'Ajouter le comité local',
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
        $builder->get('committees')->addModelTransformer($this->committeeTransformer);
        $builder->get('address')->remove('address');

        if ($citizenProject instanceof CitizenProject && $citizenProject->hasImageUploaded()) {
            $builder->add('remove_image', CheckboxType::class, [
                'property_path' => 'removeImage',
                'required' => false,
            ]);
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
