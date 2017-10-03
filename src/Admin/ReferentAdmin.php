<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ReferentAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('gender')
            ->add('emailAddress')
            ->add('slug')
            ->add('facebookPageUrl')
            ->add('twitterPageUrl')
            ->add('donationPageUrl')
            ->add('websiteUrl')
            ->add('geojson')
            ->add('description')
            ->add('firstName')
            ->add('lastName')
            ->add('displayMedia')
            ->add('managedArea.codes')
            ->add('managedArea.markerLatitude')
            ->add('managedArea.markerLongitude')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('gender')
            ->add('emailAddress')
            ->add('slug')
            ->add('facebookPageUrl')
            ->add('twitterPageUrl')
            ->add('donationPageUrl')
            ->add('websiteUrl')
            ->add('geojson')
            ->add('description')
            ->add('firstName')
            ->add('lastName')
            ->add('displayMedia')
            ->add('managedArea.codes')
            ->add('managedArea.markerLatitude')
            ->add('managedArea.markerLongitude')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                ),
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id')
            ->add('gender')
            ->add('emailAddress')
            ->add('slug')
            ->add('facebookPageUrl')
            ->add('twitterPageUrl')
            ->add('donationPageUrl')
            ->add('websiteUrl')
            ->add('geojson')
            ->add('description')
            ->add('firstName')
            ->add('lastName')
            ->add('displayMedia')
            ->add('managedArea.codes')
            ->add('managedArea.markerLatitude')
            ->add('managedArea.markerLongitude')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('gender')
            ->add('emailAddress')
            ->add('slug')
            ->add('facebookPageUrl')
            ->add('twitterPageUrl')
            ->add('donationPageUrl')
            ->add('websiteUrl')
            ->add('geojson')
            ->add('description')
            ->add('firstName')
            ->add('lastName')
            ->add('displayMedia')
            ->add('managedArea.codes')
            ->add('managedArea.markerLatitude')
            ->add('managedArea.markerLongitude')
        ;
    }
}
