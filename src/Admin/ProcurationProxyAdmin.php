<?php

namespace App\Admin;

use App\Address\AddressInterface;
use App\Entity\ElectionRound;
use App\Entity\ProcurationProxy;
use App\Form\GenderType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProcurationProxyAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    /**
     * @param ProcurationProxy $object
     */
    protected function preUpdate(object $object): void
    {
        parent::preUpdate($object);

        $object->processAvailabilities();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
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
            ->with('Statut', ['class' => 'col-md-6'])
                ->add('reliability', NumberType::class, [
                    'label' => 'Fiabilité',
                    'help' => '-1 : caché aux responsables procuration, 0 à 5 : score de fiabilité',
                ])
                ->add('reliabilityDescription', null, [
                    'label' => 'Description',
                    'help' => 'Description associée au mandataire pour les responsables procuration',
                ])
                ->add('disabled', null, [
                    'label' => 'Est désactivé',
                    'help' => 'Car plus disponible ou pas fiable',
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
            ->with('Tours', ['class' => 'col-md-6'])
                ->add('proxiesCount', null, [
                    'label' => 'Nombre de procurations proposées',
                    'disabled' => $this->getSubject()->getFoundRequests()->count() > 0,
                ])
                ->add('electionRounds', EntityType::class, [
                    'label' => 'Proposés',
                    'class' => ElectionRound::class,
                    'multiple' => true,
                    'disabled' => $this->getSubject()->getFoundRequests()->count() > 0,
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

        if (AddressInterface::FRANCE !== $this->getSubject()->getCountry()) {
            $show
                ->add('stage', null, [
                    'label' => 'État/Province',
                ])
            ;
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
            ->with('Tours', ['class' => 'col-md-6'])
                ->add('proxiesCount', null, [
                    'label' => 'Nombre de procurations proposées',
                ])
                ->add('electionRounds', 'array', [
                    'label' => 'Proposés',
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
            ->add('electionRounds', null, [
                'label' => 'Tours',
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
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
            ->add('reliability', null, [
                'label' => 'Fiabilité',
            ])
            ->add('reliabilityDescription', null, [
                'label' => 'Raison de la fiabilité',
            ])
            ->add('_profile', null, [
                'virtual_field' => true,
                'label' => 'Proposition',
                'template' => 'admin/procuration/proxy_list_summary.html.twig',
            ])
            ->add('_status', null, [
                'label' => 'Statut',
                'virtual_field' => true,
                'template' => 'admin/procuration/proxy_list_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/procuration/proxy_list_actions.html.twig',
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        return [
            'gender',
            'firstNames',
            'lastName',
            'emailAddress',
            'birthdate',
            'voterNumber',
            'reachable',
            'reliability',
            'reliabilityDescription',
            'address',
            'postalCode',
            'cityName',
            'country',
            'phone',
            'votePostalCode',
            'voteCityName',
            'voteCountry',
            'voteOffice',
            'electionRounds',
            'disabled',
            'disabledReason',
            'otherVoteCities',
        ];
    }
}
