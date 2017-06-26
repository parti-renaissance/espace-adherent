<?php

namespace AppBundle\Form;

use AppBundle\Entity\MissionTypeEntityType;
use AppBundle\Entity\Summary;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SummaryType extends AbstractType
{
    const STEP_SYNTHESIS = 'synthèse';
    const STEP_MISSION_WISHES = 'missions';
    const STEP_MOTIVATION = 'motivation';
    const STEP_SKILLS = 'compétences';
    const STEP_INTERESTS = 'centre d\'intérêts';
    const STEP_CONTACT = 'contact';

    const STEPS = [
        self::STEP_SYNTHESIS,
        self::STEP_MISSION_WISHES,
        self::STEP_MOTIVATION,
        self::STEP_SKILLS,
        self::STEP_INTERESTS,
        self::STEP_CONTACT,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['step']) {
            case self::STEP_SYNTHESIS:
                $builder
                    ->add('current_profession', TextType::class, [
                        'required' => false,
                        'empty_data' => null,
                    ])
                    ->add('current_position', ActivityPositionType::class)
                    ->add('contribution_wish', ContributionChoiceType::class)
                    ->add('availabilities', JobDurationChoiceType::class)
                    ->add('job_locations', JobLocationChoiceType::class)
                    ->add('professional_synopsis', TextareaType::class)
                ;
                break;

            case self::STEP_MISSION_WISHES:
                $builder
                    ->add('mission_type_wishes', MissionTypeEntityType::class)
                ;
                break;

            case self::STEP_MOTIVATION:
                $builder
                    ->add('motivation', TextareaType::class)
                ;
                break;

            case self::STEP_SKILLS:
                $builder
                    ->add('skills', CollectionType::class, [
                        'entry_type' => SkillType::class,
                        'allow_add' => true,
                        'allow_delete' => true,
                    ])
                ;
                break;

            case self::STEP_INTERESTS:
                $builder
                    ->add('member_interests', MemberInterestsChoiceType::class)
                ;
                break;

            case self::STEP_CONTACT:
                $builder
                    ->add('contact_email', EmailType::class)
                    ->add('linked_in_url', UrlType::class, [
                        'required' => false,
                    ])
                    ->add('website_url', UrlType::class, [
                        'required' => false,
                    ])
                    ->add('facebook_url', UrlType::class, [
                        'required' => false,
                    ])
                    ->add('twitter_nickname', UrlType::class, [
                        'required' => false,
                    ])
                    ->add('viadeo_url', UrlType::class, [
                        'required' => false,
                    ])
                ;
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Summary::class,
                'error_mapping' => [
                    'validAvailabilities' => 'availabilities',
                    'validJobLocations' => 'job_locations',
                ],
            ])
            ->setRequired('step')
            ->setAllowedTypes('step', self::STEPS)
        ;
    }
}
