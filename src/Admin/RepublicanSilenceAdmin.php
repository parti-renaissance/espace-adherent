<?php

namespace App\Admin;

use App\Entity\RepublicanSilence;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DateTimePickerType;

class RepublicanSilenceAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('zones', 'array', ['label' => 'silence_republican.zones.label'])
            ->add('beginAt', null, ['label' => 'common.begin_at'])
            ->add('finishAt', null, ['label' => 'common.finish_at'])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Général', ['class' => 'col-md-6', 'description' => 'silence_republican.help'])
                ->add('zones', null, ['label' => 'silence_republican.zones.label'])
                ->add('beginAt', DateTimePickerType::class, [
                    'label' => 'common.begin_at',
                ])
                ->add('finishAt', DateTimePickerType::class, [
                    'label' => 'common.finish_at',
                ])
            ->end()
        ;
    }

    protected function configureBatchActions($actions)
    {
        return [];
    }

    /**
     * @param RepublicanSilence $object
     *
     * @return string
     */
    public function toString($object)
    {
        return implode(', ', $object->getZones()->toArray());
    }
}
