<?php

namespace AppBundle\Admin;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Form\GenderType;
use AppBundle\Form\UnitedNationsCountryType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
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

    public function getTemplate($name)
    {
        if ('list' === $name) {
            return 'admin/procuration/list_invitations_link.html.twig';
        }

        return parent::getTemplate($name);
    }

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
                    'choices' => [
                        'Parce que je réside dans une commune différente de celle où je suis inscrit(e) sur une liste électorale' => ProcurationRequest::REASON_RESIDENCY,
                        'Parce que je suis en vacances' => ProcurationRequest::REASON_HOLIDAYS,
                        'En raison d’obligations professionnelles' => ProcurationRequest::REASON_PROFESSIONAL,
                        'En raison d’un handicap' => ProcurationRequest::REASON_HANDICAP,
                        'Pour raison de santé' => ProcurationRequest::REASON_HEALTH,
                        'En raison d’assistance apportée à une personne malade ou infirme' => ProcurationRequest::REASON_HELP,
                        'En raison d’obligations de formation' => ProcurationRequest::REASON_TRAINING,
                    ],
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
