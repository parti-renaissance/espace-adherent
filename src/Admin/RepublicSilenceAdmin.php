<?php

namespace AppBundle\Admin;

use AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;

class RepublicSilenceAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('zones', 'array')
            ->add('beginAt', null, ['label' => 'common.begin_at'])
            ->add('finishAt', null, ['label' => 'common.finish_at'])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('General', ['class' => 'col-md-6'])
                ->add('zones')
                ->add('beginAt', DateTimePickerType::class, [
                    'label' => 'common.begin_at'
                ])
                ->add('finishAt', DateTimePickerType::class, [
                    'label' => 'common.finish_at'
                ])
            ->end()
        ;

        $form->getFormBuilder()->get('zones')->addModelTransformer(new ArrayToStringTransformer());
    }

    public function toString($object)
    {
        return implode(', ', $object->getZones());
    }
}
