<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Form\ActivityPositionType;
use AppBundle\Form\GenderType;
use AppBundle\Intl\UnitedNationsBundle;
use Doctrine\ORM\QueryBuilder;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdherentAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Informations personnelles', array('class' => 'col-md-8'))
                ->add('status', null, [
                    'label' => 'Etat du compte',
                ])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
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
                ->add('position', null, [
                    'label' => 'Statut',
                ])
            ->end()
            ->with('Référent', array('class' => 'col-md-4'))
                ->add('isReferent', 'boolean', [
                    'label' => 'Est référent ?',
                ])
                ->add('managedAreaCodesAsString', null, [
                    'label' => 'Codes des zones gérés',
                ])
                ->add('managedAreaMarkerLatitude', null, [
                    'label' => 'Latitude du point sur la carte',
                ])
                ->add('managedAreaMarkerLongitude', null, [
                    'label' => 'Longitude du point sur la carte',
                ])
            ->end()
            ->with('Préférences des e-mails', array('class' => 'col-md-8'))
                ->add('hasSubscribedMainEmails', 'boolean', [
                    'label' => 'Abonné aux mails nationaux ?',
                ])
                ->add('hasSubscribedReferentsEmails', 'boolean', [
                    'label' => 'Abonné aux mails de référents ?',
                ])
                ->add('hasSubscribedLocalHostEmails', 'boolean', [
                    'label' => 'Abonné aux mails de comités ?',
                ])
            ->end()
            ->with('Responsable procuration', array('class' => 'col-md-4'))
                ->add('isProcurationManager', 'boolean', [
                    'label' => 'Est responsable procuration ?',
                ])
                ->add('procurationManagedAreaCodesAsString', null, [
                    'label' => 'Codes des zones gérés',
                ])
            ->end();
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations personnelles', array('class' => 'col-md-8'))
                ->add('status', ChoiceType::class, [
                    'label' => 'Etat du compte',
                    'choices' => [
                        'Activé' => Adherent::ENABLED,
                        'Désactivé' => Adherent::DISABLED,
                    ],
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'required' => false,
                ])
                ->add('birthdate', 'sonata_type_date_picker', [
                    'label' => 'Date de naissance',
                    'required' => false,
                ])
                ->add('position', ActivityPositionType::class, [
                    'label' => 'Statut',
                ])
            ->end()
            ->with('Référent', array('class' => 'col-md-4'))
                ->add('managedArea.codesAsString', TextType::class, [
                    'label' => 'Codes des zones gérés',
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas référent. '.
                        'Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.',
                ])
                ->add('managedArea.markerLatitude', TextType::class, [
                    'label' => 'Latitude du point sur la carte des référents',
                    'required' => false,
                ])
                ->add('managedArea.markerLongitude', TextType::class, [
                    'label' => 'Longitude du point sur la carte des référents',
                    'required' => false,
                ])
            ->end()
            ->with('Responsable procuration', array('class' => 'col-md-4'))
                ->add('procurationManagedAreaCodesAsString', TextType::class, [
                    'label' => 'Codes des zones gérés',
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas responsable procuration. '.
                        'Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.',
                ])
            ->end()
            ->with('Préférences des e-mails', array('class' => 'col-md-4'))
                ->add('hasSubscribedMainEmails', CheckboxType::class, [
                    'label' => 'Abonné aux mails nationaux ?',
                    'required' => false,
                ])
                ->add('hasSubscribedReferentsEmails', CheckboxType::class, [
                    'label' => 'Abonné aux mails de référents ?',
                    'required' => false,
                ])
                ->add('hasSubscribedLocalHostEmails', CheckboxType::class, [
                    'label' => 'Abonné aux mails de comités ?',
                    'required' => false,
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('registeredAt', 'doctrine_orm_date_range', [
                'label' => 'Date d\'adhésion',
                'field_type' => 'sonata_type_date_range_picker',
            ])
            ->add('postalCode', 'doctrine_orm_callback', [
                'label' => 'Code postal',
                'field_type' => 'text',
                'callback' => function ($qb, $alias, $field, $value) {
                    /* @var QueryBuilder $qb */

                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('%s.postAddress.postalCode', $alias).' LIKE :postalCode');
                    $qb->setParameter('postalCode', $value['value'].'%');

                    return true;
                },
            ])
            ->add('city', 'doctrine_orm_callback', [
                'label' => 'Ville',
                'field_type' => 'text',
                'callback' => function ($qb, $alias, $field, $value) {
                    /* @var QueryBuilder $qb */

                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
                    $qb->setParameter('cityName', '%'.strtolower($value['value']).'%');

                    return true;
                },
            ])
            ->add('country', 'doctrine_orm_callback', [
                'label' => 'Pays',
                'field_type' => 'choice',
                'field_options' => [
                    'choices' => array_flip(UnitedNationsBundle::getCountries()),
                ],
                'callback' => function ($qb, $alias, $field, $value) {
                    /* @var QueryBuilder $qb */

                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.country)', $alias).' = :country');
                    $qb->setParameter('country', strtolower($value['value']));

                    return true;
                },
            ])
            ->add('referent', 'doctrine_orm_callback', [
                'show_filter' => true,
                'label' => 'N\'afficher que les référents',
                'field_type' => 'checkbox',
                'callback' => function ($qb, $alias, $field, $value) {
                    /* @var QueryBuilder $qb */

                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('%s.managedArea.codes', $alias).' IS NOT NULL');

                    return true;
                },
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/adherent_phone.html.twig',
            ])
            ->add('postAddress.postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('postAddress.cityName', null, [
                'label' => 'Ville',
            ])
            ->add('postAddress.country', null, [
                'label' => 'Pays',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'adhésion',
            ])
            ->add('type', TextType::class, [
                'label' => 'Type',
                'template' => 'admin/adherent_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/adherent_actions.html.twig',
            ])
        ;
    }
}
