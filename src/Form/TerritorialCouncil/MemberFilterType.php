<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\Committee;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Form\GenderType;
use App\Repository\CommitteeRepository;
use App\Repository\ElectedRepresentative\ZoneRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use App\TerritorialCouncil\Filter\MembersListFilter;
use App\ValueObject\Genders;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('territorialCouncils', EntityType::class, [
                'label' => 'referent.territorial_councils',
                'class' => TerritorialCouncil::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (TerritorialCouncilRepository $tcRepository) use ($options) {
                    return $tcRepository->createSelectByReferentTagsQueryBuilder($options['referent_tags']);
                },
            ])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('gender', GenderType::class, [
                'placeholder' => 'common.all',
                'expanded' => true,
                'required' => false,
                'choices' => [
                    'common.gender.woman' => Genders::FEMALE,
                    'common.gender.man' => Genders::MALE,
                    'common.gender.unknown' => Genders::UNKNOWN,
                ],
            ])
            ->add('ageMin', IntegerType::class, ['required' => false])
            ->add('ageMax', IntegerType::class, ['required' => false])
            ->add('qualities', ChoiceType::class, [
                'label' => 'referent.territorial_council.quality',
                'choices' => TerritorialCouncilQualityEnum::ALL,
                'choice_label' => function (string $choice) {
                    return "territorial_council.membership.quality.$choice";
                },
                'required' => false,
                'multiple' => true,
            ])
            ->add('cities', EntityType::class, [
                'label' => 'referent.territorial_council.city',
                'class' => Zone::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (ZoneRepository $zoneRepository) use ($options) {
                    return $zoneRepository->createSelectByReferentTagsQueryBuilder($options['referent_tags']);
                },
            ])
            ->add('committees', EntityType::class, [
                'label' => 'referent.territorial_council.committee',
                'class' => Committee::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (CommitteeRepository $repository) use ($options) {
                    return $repository->createSelectByReferentTagsQueryBuilder($options['referent_tags']);
                },
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
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
        ;
    }

    public function getBlockPrefix()
    {
        return 'f';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => MembersListFilter::class,
                'referent_tags' => [],
                'allow_extra_fields' => true,
            ])
            ->setAllowedTypes('referent_tags', 'array')
        ;
    }
}
