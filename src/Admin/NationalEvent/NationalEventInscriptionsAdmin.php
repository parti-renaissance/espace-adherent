<?php

namespace App\Admin\NationalEvent;

use App\Address\AddressInterface;
use App\Admin\AbstractAdmin;
use App\Form\CivilityType;
use App\NationalEvent\InscriptionStatusEnum;
use App\Query\Utils\MultiColumnsSearchHelper;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class NationalEventInscriptionsAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('search', CallbackFilter::class, [
                'label' => 'Recherche',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ["$alias.firstName", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstName"],
                            ["$alias.addressEmail", "$alias.addressEmail"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                        ]
                    );

                    return true;
                },
            ])
            ->add('event', null, ['label' => 'Event', 'show_filter' => true])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_combine(InscriptionStatusEnum::toArray(), InscriptionStatusEnum::toArray()),
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('uuid', null, ['label' => 'Uuid'])
            ->add('event', null, ['label' => 'Event'])
            ->add('gender', null, ['label' => 'Civilité'])
            ->add('firstName', null, ['label' => 'Prénom'])
            ->add('lastName', null, ['label' => 'Nom'])
            ->add('addressEmail', null, ['label' => 'E-mail'])
            ->add('postalCode', null, ['label' => 'Code postal'])
            ->add('status', 'trans', ['label' => 'Statut'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => []]])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('status', ChoiceType::class, ['label' => 'Statut', 'choices' => array_combine(InscriptionStatusEnum::toArray(), InscriptionStatusEnum::toArray())])
                ->add('gender', CivilityType::class, ['label' => 'Civilité'])
                ->add('firstName', null, ['label' => 'Prénom'])
                ->add('lastName', null, ['label' => 'Nom'])
                ->add('postalCode', null, ['label' => 'Code postal'])
                ->add('birthdate', null, ['label' => 'Date de naissance', 'widget' => 'single_text'])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'required' => false,
                    'preferred_country_choices' => [AddressInterface::FRANCE],
                ])
            ->end()
            ->with('Informations additionnelles', ['class' => 'col-md-6'])
                ->add('event', null, ['label' => 'Event', 'disabled' => true])
                ->add('uuid', null, ['label' => 'Uuid', 'disabled' => true])
                ->add('addressEmail', null, ['label' => 'E-mail', 'disabled' => true])
                ->add('utmSource', null, ['label' => 'UTM Source', 'disabled' => true])
                ->add('utmCampaign', null, ['label' => 'UTM Campagne', 'disabled' => true])
                ->add('ticketSentAt', null, ['label' => 'Date d\'envoi du billet', 'widget' => 'single_text', 'disabled' => true])
            ->end()
        ;
    }
}
