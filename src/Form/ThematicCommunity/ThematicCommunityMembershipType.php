<?php

namespace App\Form\ThematicCommunity;

use App\Address\Address;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Entity\ThematicCommunity\ThematicCommunityToUserListDefinitionEnum;
use App\Entity\UserListDefinition;
use App\Form\ActivityPositionType;
use App\Form\AddressType;
use App\Form\DatePickerType;
use App\Form\GenderType;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityRepository;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints;

class ThematicCommunityMembershipType extends AbstractType
{
    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->security->getUser()) {
            $builder
                ->add('firstName', TextType::class, [
                    'label' => 'Prénom',
                    'attr' => ['placeholder' => 'Prénom'],
                    'constraints' => [
                        new Constraints\NotBlank(),
                    ],
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'Nom',
                    'attr' => ['placeholder' => 'Nom'],
                    'constraints' => [
                        new Constraints\NotBlank(),
                    ],
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                    'attr' => ['placeholder' => 'Prénom'],
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\Choice(['choices' => Genders::ALL]),
                    ],
                ])
                ->add('customGender', TextType::class, [
                    'required' => false,
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'attr' => ['placeholder' => 'Email'],
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\Email(),
                    ],
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'required' => false,
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'default_region' => Address::FRANCE,
                    'preferred_country_choices' => [Address::FRANCE],
                ])
                ->add('birthDate', DatePickerType::class, [
                    'label' => 'Date de naissance',
                    'max_date' => new \DateTime('-15 years'),
                    'min_date' => new \DateTime('-120 years'),
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\Date(),
                    ],
                ])
                ->add('position', ActivityPositionType::class, [
                    'label' => 'Statut professionel',
                    'placeholder' => 'Je suis',
                    'constraints' => [
                        new Constraints\NotBlank(),
                    ],
                ])
                ->add('postAddress', AddressType::class, [
                    'label' => false,
                    'child_error_bubbling' => false,
                    'data' => $builder->getData() ? Address::createFromAddress($builder->getData()->getPostAddress()) : null,
                ])
            ;
        }

        $builder
            ->add('hasJob', ChoiceType::class, [
                'label' => 'Exercez-vous un métier en lien avec cette communauté thématique ?',
                'choices' => [
                    'Oui' => 1,
                    'Non' => 0,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('job', TextType::class, [
                'label' => 'Métier',
                'attr' => ['placeholder' => 'Métier'],
                'required' => false,
            ])
            ->add('association', ChoiceType::class, [
                'label' => 'Êtes-vous dans une association en lien avec la communauté thématique ?',
                'choices' => [
                    'Oui' => 1,
                    'Non' => 0,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('associationName', TextType::class, [
                'label' => 'Nom de l\'association',
                'required' => false,
                'attr' => ['placeholder' => 'Association'],
            ])
            ->add('motivations', ChoiceType::class, [
                'label' => 'Comment souhaitez-vous vous impliquer au sein de la communauté ?',
                'expanded' => false,
                'multiple' => true,
                'choices' => ThematicCommunityMembership::MOTIVATIONS,
                'choice_label' => static function ($choice) {
                    return 'admin.thematic_community.membership.motivations.'.$choice;
                },
            ])
        ;

        if (!\in_array($builder->getData()->getCommunity()->getSlug(), ['sante', 'europe'], true)) {
            $builder->add('userListDefinitions', EntityType::class, [
                'label' => 'thematic_community.userListDefinitions.label.'.$builder->getData()->getCommunity()->getSlug(),
                'class' => UserListDefinition::class,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($builder) {
                    return $er->createQueryBuilder('uld')
                        ->where('uld.type = :type')
                        ->setParameter('type', ThematicCommunityToUserListDefinitionEnum::MAP[$builder->getData()->getCommunity()->getName()] ?? null)
                    ;
                },
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ThematicCommunityMembership::class,
        ]);
    }
}
