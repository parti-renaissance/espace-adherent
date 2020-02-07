<?php

namespace AppBundle\Admin;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Form\GenderType;
use AppBundle\Form\UnitedNationsCountryType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProcurationRequestAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected $formOptions = [
        'validation_groups' => ['admin'],
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Coordonnées', ['class' => 'col-md-6'])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'Nom de naissance',
                    'filter_emojis' => true,
                ])
                ->add('firstNames', TextType::class, [
                    'label' => 'Prénom(s)',
                    'filter_emojis' => true,
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                ])
                ->add('birthdate', 'sonata_type_date_picker', [
                    'label' => 'Date de naissance',
                ])
                ->add('country', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('cityName', TextType::class, [
                    'label' => 'Ville',
                    'filter_emojis' => true,
                ])
                ->add('address', TextType::class, [
                    'label' => 'Adresse postale',
                    'filter_emojis' => true,
                ])
            ->end()
            ->with('Lieu de vote', ['class' => 'col-md-6'])
                ->add('voteCountry', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('votePostalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('voteCityName', TextType::class, [
                    'label' => 'Ville',
                    'filter_emojis' => true,
                ])
                ->add('voteOffice', null, [
                    'label' => 'Bureau de vote',
                ])
            ->end()
            ->with('Procuration', ['class' => 'col-md-6'])
                ->add('electionRounds', null, [
                    'label' => 'Tours',
                ])
                ->add('reason', ChoiceType::class, [
                    'label' => 'Raison',
                    'choices' => [
                        ProcurationRequest::REASON_RESIDENCY,
                        ProcurationRequest::REASON_HOLIDAYS,
                        ProcurationRequest::REASON_PROFESSIONAL,
                        ProcurationRequest::REASON_HANDICAP,
                        ProcurationRequest::REASON_HEALTH,
                        ProcurationRequest::REASON_HELP,
                        ProcurationRequest::REASON_TRAINING,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'procuration_request.reasons.'.$choice;
                    },
                ])
                ->add('requestFromFrance', ChoiceType::class, [
                    'label' => 'Établissement de la procuration',
                    'choices' => [
                        'France' => true,
                        'Étranger' => false,
                    ],
                    'expanded' => true,
                ])
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
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
                    'label' => 'Adresse e-mail',
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

        if ('FR' != $this->getSubject()->getCountry()) {
            $showMapper
                 ->add('stage', null, [
                     'label' => 'État/Province',
                 ])
             ;
        }

        $showMapper->end();

        $showMapper
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
                ->add('reason', null, [
                    'label' => 'Raison',
                    'template' => 'admin/procuration/request_show_reason.html.twig',
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
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
                'label' => 'Adresse e-mail',
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
                'label' => 'Adresse e-mail',
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
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/procuration/request_list_actions.html.twig',
            ])
        ;
    }
}
