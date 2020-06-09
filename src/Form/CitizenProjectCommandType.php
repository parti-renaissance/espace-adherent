<?php

namespace App\Form;

use App\CitizenProject\CitizenProjectCommand;
use App\CitizenProject\CitizenProjectCreationCommand;
use App\Entity\CitizenProject;
use App\Entity\CitizenProjectCategory;
use App\Entity\CitizenProjectSkill;
use App\Form\DataTransformer\CommitteeTransformer;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CitizenProjectCommandType extends AbstractType
{
    private $committeeTransformer;

    public function __construct(CommitteeTransformer $committeeTransformer)
    {
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
                'disabled' => $command->isCitizenProjectApproved() || $options['from_turnkey_project'],
            ])
            ->add('subtitle', TextType::class, [
                'filter_emojis' => true,
                'format_title_case' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 80],
                'disabled' => $options['from_turnkey_project'],
            ])
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('problem_description', TextareaType::class, [
                'property_path' => 'problemDescription',
                'filter_emojis' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
                'disabled' => $options['from_turnkey_project'],
            ])
            ->add('proposed_solution', PurifiedTextareaType::class, [
                'property_path' => 'proposedSolution',
                'filter_emojis' => true,
                'purifier_type' => 'enrich_content',
                'with_character_count' => true,
                'attr' => ['maxlength' => 800, 'from_turnkey_project' => $options['from_turnkey_project']],
                'disabled' => $options['from_turnkey_project'],
            ])
            ->add('required_means', TextareaType::class, [
                'property_path' => 'requiredMeans',
                'filter_emojis' => true,
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
            ])
            ->add('address', NullableAddressType::class)
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('skills', EntityType::class, [
                'label' => 'Choisissez le thème de projet pour faire apparaître les compétences associées',
                'class' => CitizenProjectSkill::class,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
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
        ;

        if ($command instanceof CitizenProjectCreationCommand) {
            $builder
                ->add('cgu', CheckboxType::class, [
                    'mapped' => false,
                    'constraints' => [new Assert\IsTrue([
                        'message' => 'common.checkbox.is_true',
                    ])],
                ])
                ->add('data_processing', CheckboxType::class, [
                    'mapped' => false,
                    'constraints' => [new Assert\IsTrue([
                        'message' => 'common.checkbox.is_true',
                    ])],
                ])
            ;
        }

        if ($options['from_turnkey_project']) {
            $builder
                ->add('category', EventCategoryType::class, [
                    'disabled' => true,
                    'class' => CitizenProjectCategory::class,
                ])
                ->add('district', TextType::class, [
                    'required' => false,
                    'filter_emojis' => true,
                    'with_character_count' => true,
                    'attr' => ['maxlength' => 50],
                ])
            ;
        } else {
            $builder
                ->add('category', EventCategoryType::class, [
                    'placeholder' => 'Choisir un thème',
                    'class' => CitizenProjectCategory::class,
                ])
            ;
        }

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
                'from_turnkey_project' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'citizen_project';
    }
}
