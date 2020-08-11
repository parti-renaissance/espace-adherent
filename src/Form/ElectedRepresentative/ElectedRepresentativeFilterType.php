<?php

namespace App\Form\ElectedRepresentative;

use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\ElectedRepresentative\ElectedRepresentativeTypeEnum;
use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\ElectedRepresentative\ZoneCategory;
use App\Entity\UserListDefinition;
use App\Form\GenderType;
use App\Repository\ElectedRepresentative\ZoneRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectedRepresentativeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('contactType', ChoiceType::class, [
                'placeholder' => 'common.all',
                'choices' => ElectedRepresentativeTypeEnum::ALL,
                'choice_label' => function (string $choice) {
                    return "elected_representative.filter.contact_type.$choice";
                },
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ])
            ->add('labels', ChoiceType::class, [
                'label' => 'elected_representative.labels',
                'choices' => LabelNameEnum::ALL,
                'choice_label' => function (string $choice) {
                    return $choice;
                },
                'required' => false,
                'multiple' => true,
            ])
            ->add('mandates', ChoiceType::class, [
                'choices' => MandateTypeEnum::CHOICES,
                'label' => 'elected_representative.mandates',
                'required' => false,
                'multiple' => true,
            ])
            ->add('politicalFunctions', ChoiceType::class, [
                'label' => 'elected_representative.political_functions',
                'choices' => PoliticalFunctionNameEnum::CHOICES,
                'required' => false,
                'multiple' => true,
            ])
            ->add('cities', EntityType::class, [
                'label' => 'elected_representative.cities',
                'class' => Zone::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (ZoneRepository $zoneRepository) use ($options) {
                    return $zoneRepository
                        ->createQueryBuilder('zone')
                        ->leftJoin('zone.category', 'category')
                        ->leftJoin('zone.referentTags', 'referentTag')
                        ->where('category.name = :category')
                        ->andWhere('referentTag IN (:tags)')
                        ->setParameters([
                            'tags' => $options['referent_tags'],
                            'category' => ZoneCategory::CITY,
                        ])
                        ->orderBy('zone.name')
                    ;
                },
            ])
            ->add('userListDefinitions', EntityType::class, [
                'label' => 'elected_representative.user_list_definitions',
                'class' => UserListDefinition::class,
                'required' => false,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er
                        ->createQueryBuilder('uld')
                        ->orderBy('uld.label')
                        ->where('uld.type IN (:type)')
                        ->setParameter('type', $options['user_list_definition_type'] ?? [])
                    ;
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
                'data_class' => ListFilter::class,
                'user_list_definition_type' => null,
                'referent_tags' => [],
                'allow_extra_fields' => true,
            ])
            ->setAllowedTypes('user_list_definition_type', 'array')
            ->setAllowedTypes('referent_tags', 'array')
        ;
    }
}
