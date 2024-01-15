<?php

namespace App\Admin;

use App\Address\AddressInterface;
use App\Form\GenderType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProcurationRequestAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Coordonnées', ['class' => 'col-md-6'])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'Nom de naissance',
                ])
                ->add('firstNames', TextType::class, [
                    'label' => 'Prénom(s)',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse email',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                ])
                ->add('birthdate', DatePickerType::class, [
                    'label' => 'Date de naissance',
                ])
                ->add('country', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('address', TextType::class, [
                    'label' => 'Adresse postale',
                ])
            ->end()
            ->with('Lieu de vote', ['class' => 'col-md-6'])
                ->add('voteCountry', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('votePostalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('voteCityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('voteOffice', null, [
                    'label' => 'Bureau de vote',
                ])
            ->end()
            ->with('Procuration', ['class' => 'col-md-6'])
                ->add('electionRounds', null, [
                    'label' => 'Tours',
                ])
                ->add('requestFromFrance', ChoiceType::class, [
                    'label' => 'Établissement de la procuration',
                    'choices' => [
                        'France' => true,
                        'Étranger' => false,
                    ],
                    'expanded' => true,
                ])
                ->add('enabled', null, [
                    'label' => 'Active',
                ])
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Coordonnées', ['class' => 'col-md-4'])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom de naissance',
                ])
                ->add('firstNames', null, [
                    'label' => 'Prénom(s)',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse email',
                ])
                ->add('phone', null, [
                    'label' => 'Téléphone',
                ])
                ->add('birthdate', null, [
                    'label' => 'Date de naissance',
                ])
                ->add('country', null, [
                    'label' => 'Pays',
                ])
                ->add('postalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('cityName', null, [
                    'label' => 'Ville',
                ])
                ->add('address', null, [
                    'label' => 'Adresse postale',
                ])
        ;

        if (AddressInterface::FRANCE != $this->getSubject()->getCountry()) {
            $show->add('stage', null, ['label' => 'État/Province']);
        }

        $show->end();

        $show
            ->with('Lieu de vote', ['class' => 'col-md-4'])
                ->add('voteCountry', null, [
                    'label' => 'Pays',
                ])
                ->add('votePostalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('voteCityName', null, [
                    'label' => 'Ville',
                ])
                ->add('voteOffice', null, [
                    'label' => 'Bureau de vote',
                ])
            ->end()
            ->with('Procuration', ['class' => 'col-md-4'])
                ->add('electionRounds', null, [
                    'label' => 'Tours',
                ])
                ->add('requestFromFrance', null, [
                    'label' => 'Établissement de la procuration',
                    'template' => 'admin/procuration/request_show_requestFromFrance.html.twig',
                ])
            ->end()
            ->with('Mailing', ['class' => 'col-md-4'])
                ->add('reachable', null, [
                    'label' => 'Peut être recontacté',
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
            ->add('lastName', null, [
                'label' => 'Nom de naissance',
            ])
            ->add('firstNames', null, [
                'label' => 'Prénom(s)',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse email',
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('lastName', null, [
                'label' => 'Nom de naissance',
            ])
            ->add('firstNames', null, [
                'label' => 'Prénom(s)',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse email',
            ])
            ->add('_profile', null, [
                'virtual_field' => true,
                'label' => 'Demande',
                'template' => 'admin/procuration/request_list_summary.html.twig',
            ])
            ->add('_status', null, [
                'label' => 'Statut',
                'virtual_field' => true,
                'template' => 'admin/procuration/request_list_status.html.twig',
            ])
            ->add('enabled', null, [
                'editable' => true,
                'label' => 'Active',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/procuration/request_list_actions.html.twig',
            ])
        ;
    }
}
