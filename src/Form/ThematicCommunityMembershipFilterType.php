<?php

namespace App\Form;

use App\Entity\JobEnum;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use App\ThematicCommunity\ThematicCommunityMembershipFilter;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThematicCommunityMembershipFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('gender', GenderType::class, [
                'placeholder' => 'Tous',
                'expanded' => true,
                'required' => false,
            ])
            ->add('ageMin', IntegerType::class, ['required' => false])
            ->add('ageMax', IntegerType::class, ['required' => false])
            ->add('city', TextType::class, ['required' => false])
            ->add('country', CountryType::class, ['required' => false])
            ->add('role', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'role_or_mandat.adherent' => 'adherent',
                    'role_or_mandat.supervisor' => 'supervisor',
                    'role_or_mandat.citizen_project_admin' => 'citizen_project_admin',
                    'role_or_mandat.referent' => 'referent',
                    'role_or_mandat.contact' => 'contact',
                ],
            ])
            ->add('emailSubscription', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'common.all' => null,
                    'common.adherent.subscribed' => true,
                    'common.adherent.unsubscribed' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ])
            ->add('smsSubscription', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'common.all' => null,
                    'common.adherent.subscribed' => true,
                    'common.adherent.unsubscribed' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ])
            ->add('joinedSince', DatePickerType::class, ['required' => false])
            ->add('joinedUntil', DatePickerType::class, ['required' => false])
            ->add('motivation', ChoiceType::class, [
                'required' => false,
                'choices' => ThematicCommunityMembership::MOTIVATIONS,
                'choice_label' => function (string $choice) {
                    return 'admin.thematic_community.membership.motivations.'.$choice;
                },
            ])
            ->add('categories', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => UserListDefinition::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('uld')
                        ->where('uld.type IN (:tc_types)')
                        ->setParameter('tc_types', UserListDefinitionEnum::THEMATIC_COMMUNITY_CODES)
                    ;
                },
            ])
            ->add('expert', ChoiceType::class, [
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Tous',
                'choices' => [
                    'Non' => 0,
                    'Oui' => 1,
                ],
            ])
            ->add('with_job', ChoiceType::class, [
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Tous',
                'choices' => [
                    'Non' => 0,
                    'Oui' => 1,
                ],
            ])
            ->add('job', ChoiceType::class, [
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'choices' => JobEnum::JOBS,
                'choice_label' => function (string $choice) {
                    return $choice;
                },
            ])
            ->add('with_association', ChoiceType::class, [
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Tous',
                'choices' => [
                    'Non' => 0,
                    'Oui' => 1,
                ],
            ])
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
        ;

        if (\count($options['handled_communities']) > 1) {
            $builder->add('thematicCommunities', EntityType::class, [
                'required' => false,
                'class' => ThematicCommunity::class,
                'multiple' => true,
                'choices' => $options['handled_communities'],
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'f';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('handled_communities')
            ->setAllowedTypes('handled_communities', 'array')
            ->setDefaults([
                'data_class' => ThematicCommunityMembershipFilter::class,
                'allow_extra_fields' => true,
                'handled_communities' => [],
            ])
        ;
    }
}
