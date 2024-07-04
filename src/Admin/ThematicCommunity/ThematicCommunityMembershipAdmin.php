<?php

namespace App\Admin\ThematicCommunity;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ThematicCommunity\AdherentMembership;
use App\Entity\ThematicCommunity\ContactMembership;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Entity\ThematicCommunity\ThematicCommunityToUserListDefinitionEnum;
use App\Entity\UserListDefinition;
use App\Form\GenderType;
use App\Form\TelNumberType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ThematicCommunityMembershipAdmin extends AbstractAdmin
{
    public $otherMemberships = [];

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('firstname', null, [
                'label' => 'Prénom',
            ])
            ->add('lastname', null, [
                'label' => 'Nom',
            ])
            ->add('email', null, [
                'label' => 'Email',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/adherent/list_phone.html.twig',
            ])
            ->add('cityWithZipcode', null, [
                'label' => 'Ville',
            ])
            ->add('community', null, [
                'label' => 'Communauté',
                'template' => 'admin/thematic_community/member/community.html.twig',
            ])
            ->add('userListDefinitions', null, [
                'label' => 'Catégories',
                'template' => 'admin/thematic_community/member/categories.html.twig',
            ])
            ->add('joinedAt', null, [
                'label' => 'Membre depuis le',
            ])
            ->add('qualifications', null, [
                'label' => 'Qualifications',
                'virtual_field' => true,
                'template' => 'admin/thematic_community/member/qualifications.html.twig',
            ])
            ->add('association', null, [
                'label' => 'Avec Association ?',
            ])
            ->add('job', null, [
                'label' => 'Métier',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $this->setOtherMemberships($form->getFormBuilder()->getDataClass());

        $isContactMembership = ContactMembership::class === $form->getFormBuilder()->getDataClass();

        $form
            ->with('Membre', ['class' => 'col-md-6'])
                ->add('firstname', TextType::class, [
                    'label' => 'Prénom',
                    'disabled' => !$isContactMembership,
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'Nom',
                    'disabled' => !$isContactMembership,
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                    'disabled' => !$isContactMembership,
                ])
                ->add('customGender', TextType::class, [
                    'required' => false,
                    'label' => 'Personnalisez votre genre',
                    'disabled' => !$isContactMembership,
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'disabled' => !$isContactMembership,
                ])
                ->add('phone', TelNumberType::class, [
                    'disabled' => !$isContactMembership,
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                    'disabled' => !$isContactMembership,
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                    'disabled' => !$isContactMembership,
                ])
                ->add('postAddress.country', CountryType::class, [
                    'label' => 'Pays',
                    'disabled' => !$isContactMembership,
                ])
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Adresse',
                    'disabled' => !$isContactMembership,
                ])
            ->end()
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('userListDefinitions', EntityType::class, [
                    'label' => 'Catégories',
                    'class' => UserListDefinition::class,
                    'multiple' => true,
                    'expanded' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('uld')
                            ->where('uld.type = :type')
                            ->setParameter('type', ThematicCommunityToUserListDefinitionEnum::MAP[$this->getSubject()->getCommunity()->getName()] ?? null)
                        ;
                    },
                ])
                ->add('hasJob', ChoiceType::class, [
                    'label' => 'Métier en lien avec la communauté ?',
                    'choices' => [
                        'Non' => 0,
                        'Oui' => 1,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                ])
                ->add('job', TextType::class, [
                    'label' => 'Métier ?',
                    'required' => false,
                ])
                ->add('association', ChoiceType::class, [
                    'label' => 'Membre d\'une association',
                    'choices' => [
                        'Non' => 0,
                        'Oui' => 1,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                ])
                ->add('associationName', TextType::class, [
                    'label' => 'Nom de l\'association',
                    'required' => false,
                ])
                ->add('motivations', ChoiceType::class, [
                    'label' => 'Modes d\'engagement',
                    'expanded' => false,
                    'multiple' => true,
                    'placeholder' => '-- Choisir un mode d\'engagement --',
                    'choices' => ThematicCommunityMembership::MOTIVATIONS,
                    'choice_label' => static function ($choice) {
                        return 'admin.thematic_community.membership.motivations.'.$choice;
                    },
                    'required' => false,
                ])
                ->add('expert', ChoiceType::class, [
                    'label' => 'Expert ?',
                    'choices' => [
                        'Non' => 0,
                        'Oui' => 1,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('type', CallbackFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ThematicCommunityMembership::TYPES,
                    'choice_label' => function ($choice) {
                        return 'admin.thematic_community_membership.type.'.$choice;
                    },
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    // no need to perform complex query if all types have been selected
                    if (\count($value->getValue()) === \count(ThematicCommunityMembership::TYPES)) {
                        return true;
                    }

                    $or = new Orx();

                    if (\in_array(ThematicCommunityMembership::TYPE_ADHERENT, $value->getValue(), true)) {
                        $qb
                            ->leftJoin("$alias.adherent", 'a')
                            ->leftJoin(ElectedRepresentative::class, 'e', Join::WITH, 'e.adherent = a')
                        ;
                        $or->add("e IS NULL AND $alias.contact IS NULL");
                    }

                    if (\in_array(ThematicCommunityMembership::TYPE_ELECTED_REPRESENTATIVE, $value->getValue(), true)) {
                        if (!\in_array('a', $qb->getAllAliases(), true)) {
                            $qb
                                ->leftJoin("$alias.adherent", 'a')
                                ->leftJoin(ElectedRepresentative::class, 'e', Join::WITH, 'e.adherent = a')
                            ;
                        }
                        $or->add("e IS NOT NULL AND $alias.contact IS NULL");
                    }

                    if (\in_array(ThematicCommunityMembership::TYPE_CONTACT, $value->getValue(), true)) {
                        $or->add("$alias.contact IS NOT NULL");
                    }

                    $qb->orWhere($or);

                    return true;
                },
            ])
            ->add('community', CallbackFilter::class, [
                'label' => 'Communauté',
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => ThematicCommunity::class,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('tc')
                            ->where('tc.enabled = 1')
                        ;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->andWhere("$alias.community IN (:value)")
                        ->setParameter('value', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('firstName', CallbackFilter::class, [
                'label' => 'Prénom',
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("adherent.$field LIKE :value OR contact.$field LIKE :value")
                        ->setParameter('value', '%'.$value->getValue().'%')
                    ;

                    return true;
                },
            ])
            ->add('lastName', CallbackFilter::class, [
                'label' => 'Nom',
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("adherent.$field LIKE :value OR contact.$field LIKE :value")
                        ->setParameter('value', '%'.$value->getValue().'%')
                    ;

                    return true;
                },
            ])
            ->add('emailAddress', CallbackFilter::class, [
                'label' => 'Email',
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("adherent.$field = :value OR contact.email = :value")
                        ->setParameter('value', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('joinedAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('cityName', CallbackFilter::class, [
                'label' => 'Ville',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("LOWER(adherent.postAddress.$field) LIKE :value OR LOWER(contact.postAddress.$field) LIKE :value")
                        ->setParameter('value', '%'.mb_strtolower($value->getValue()).'%')
                    ;

                    return true;
                },
            ])
            ->add('country', CallbackFilter::class, [
                'label' => 'Pays',
                'show_filter' => true,
                'field_type' => CountryType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("LOWER(adherent.postAddress.$field) LIKE :value OR LOWER(contact.postAddress.$field) LIKE :value")
                        ->setParameter('value', '%'.mb_strtolower($value->getValue()).'%')
                    ;

                    return true;
                },
            ])
            ->add('expert', null, [
                'label' => 'Expert ?',
                'show_filter' => true,
            ])
            ->add('association', null, [
                'label' => 'Avec association ?',
                'show_filter' => true,
            ])
            ->add('with_job', CallbackFilter::class, [
                'label' => 'Avec métier ?',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Non' => 0,
                        'Oui' => 1,
                    ],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (0 !== $value->getValue() && 1 !== $value->getValue()) {
                        return false;
                    }

                    $qb->andWhere("$alias.hasJob = :with_job")
                        ->setParameter('with_job', (bool) $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('motivation', CallbackFilter::class, [
                'label' => 'Motivation',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => ThematicCommunityMembership::MOTIVATIONS,
                    'choice_label' => static function ($choice) {
                        return 'admin.thematic_community.membership.motivations.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $or = new Orx();
                    foreach ($value->getValue() as $i => $motivation) {
                        $or->add(sprintf('FIND_IN_SET(:motivation_%d, %s.motivations) > 0', $i, $alias));
                        $qb->setParameter(sprintf('motivation_%d', $i), mb_strtolower($motivation));
                    }
                    $qb->andWhere($or);

                    return true;
                },
            ])
        ;
    }

    public function toString(object $object): string
    {
        return sprintf('Adhésion de %s à la communauté %s', $object->getFirstName().' '.$object->getLastName(), $object->getCommunity()->getName());
    }

    private function setOtherMemberships(string $class): void
    {
        switch ($class) {
            case AdherentMembership::class:
                $field = ThematicCommunityMembership::TYPE_ADHERENT;
                $object = $this->getSubject()->getAdherent();
                break;
            case ContactMembership::class:
                $field = ThematicCommunityMembership::TYPE_CONTACT;
                $object = $this->getSubject()->getContact();
                break;
            default:
                throw new \LogicException(sprintf('Class "%s" is not a valid membership class.', $class));
        }

        $this->otherMemberships = array_filter(
            $this->getModelManager()->findBy($class, [$field => $object]),
            function (ThematicCommunityMembership $membership) {
                return $membership !== $this->getSubject();
            }
        );
    }
}
