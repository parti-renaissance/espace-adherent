<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Form\ActivityPositionType;
use AppBundle\Form\GenderType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdherentAdmin extends AbstractAdmin
{
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Adhérent', array('class' => 'col-md-8'))
                ->add('isEnabled', 'boolean', [
                    'label' => 'Compte activé ?',
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
                ->add('gender', null, [
                    'label' => 'Genre',
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
                ->add('hasSubscribedMainEmails', 'boolean', [
                    'label' => 'Abonné aux mails nationaux ?',
                    'required' => false,
                ])
                ->add('hasSubscribedReferentsEmails', 'boolean', [
                    'label' => 'Abonné aux mails de référents ?',
                    'required' => false,
                ])
                ->add('hasSubscribedLocalHostEmails', 'boolean', [
                    'label' => 'Abonné aux mails de comités ?',
                    'required' => false,
                ])
                ->add('interestsAsJson', null, [
                    'label' => 'Centres d\'intérêts',
                ])
                ->add('registeredAt', null, [
                    'label' => 'Date d\'enregistrement',
                ])
                ->add('activatedAt', null, [
                    'label' => 'Date d\'activation du compte',
                ])
                ->add('lastLoggedAt', null, [
                    'label' => 'Date de dernière connexion',
                ])
                ->add('updatedAt', null, [
                    'label' => 'Date de dernière mise à jour',
                ])
            ->end()
            ->with('Référent', array('class' => 'col-md-4'))
                ->add('isReferent', 'boolean', [
                    'label' => 'Est référent ?',
                    'required' => false,
                ])
                ->add('managedAreaCodesAsString', null, [
                    'label' => 'Codes des zones gérés',
                    'required' => false,
                ])
                ->add('managedAreaMarkerLatitude', null, [
                    'label' => 'Latitude du point sur la carte',
                    'required' => false,
                ])
                ->add('managedAreaMarkerLongitude', null, [
                    'label' => 'Longitude du point sur la carte',
                    'required' => false,
                ])
            ->end();
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Adhérent', array('class' => 'col-md-8'))
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
                ->add('gender', GenderType::class)
                ->add('phone', null, [
                    'label' => 'Téléphone',
                ])
                ->add('birthdate', BirthdayType::class, [
                    'label' => 'Date de naissance',
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                ])
                ->add('position', ActivityPositionType::class)
                ->add('status', ChoiceType::class, [
                    'label' => 'Etat du compte',
                    'choices' => [
                        'Activé' => Adherent::ENABLED,
                        'Désactivé' => Adherent::DISABLED,
                    ],
                ])
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
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('hasSubscribedMainEmails', 'boolean', [
                'label' => 'Mails nationaux ?',
            ])
            ->add('hasSubscribedReferentsEmails', 'boolean', [
                'label' => 'Mails de référents ?',
            ])
            ->add('hasSubscribedLocalHostEmails', 'boolean', [
                'label' => 'Mails de comités ?',
            ])
            ->add('isEnabled', 'boolean', [
                'label' => 'Activé ?',
            ])
            ->add('isReferent', 'boolean', [
                'label' => 'Référent ?',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'impresonate' => [
                        'template' => 'admin/adherent_impersonate.html.twig',
                    ],
                    'show' => [],
                    'edit' => [],
                ],
            ]);
    }
}
