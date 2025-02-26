<?php

namespace App\Admin;

use App\Adherent\Referral\ModeEnum;
use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Entity\Adherent;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ReferralAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('identifier', null, [
                'label' => 'Numéro',
            ])
            ->add('civility', null, [
                'label' => 'Civilité',
            ])
            ->add('_fullName', null, [
                'label' => 'Prénom/Nom',
                'virtual_field' => true,
                'template' => 'admin/referral/list_fullName.html.twig',
            ])
            ->add('_contact', null, [
                'label' => 'Email/Téléphone',
                'virtual_field' => true,
                'template' => 'admin/referral/list_contact.html.twig',
            ])
            ->add('referrer', null, [
                'label' => 'Parrain',
                'template' => 'admin/referral/list_referrer.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/referral/list_type.html.twig',
            ])
            ->add('mode', 'null', [
                'label' => 'Mode',
                'template' => 'admin/referral/list_mode.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/referral/list_status.html.twig',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('identifier', null, [
                'label' => 'Numéro',
                'show_filter' => true,
            ])
            ->add('emailAddress', null, [
                'label' => 'Email (parrainé)',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom (parrainé)',
            ])
            ->add('lastName', null, [
                'label' => 'Nom (parrainé)',
            ])
            ->add('referrer', ModelFilter::class, [
                'label' => 'Parrain',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'property' => [
                        'search',
                    ],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getId()
                        );
                    },
                ],
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => EnumType::class,
                'field_options' => [
                    'class' => TypeEnum::class,
                    'choice_label' => static function (TypeEnum $type): string {
                        return 'referral.type.'.$type->value;
                    },
                    'multiple' => true,
                ],
            ])
            ->add('mode', ChoiceFilter::class, [
                'label' => 'Mode',
                'show_filter' => true,
                'field_type' => EnumType::class,
                'field_options' => [
                    'class' => ModeEnum::class,
                    'choice_label' => static function (ModeEnum $mode): string {
                        return 'referral.mode.'.$mode->value;
                    },
                    'multiple' => true,
                ],
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => EnumType::class,
                'field_options' => [
                    'class' => StatusEnum::class,
                    'choice_label' => static function (StatusEnum $status): string {
                        return 'referral.status.'.$status->value;
                    },
                    'multiple' => true,
                ],
            ])
        ;
    }
}
