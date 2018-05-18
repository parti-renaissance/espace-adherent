<?php

namespace AppBundle\Admin;

use AppBundle\Entity\RepublicanSilence;
use AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use AppBundle\RepublicanSilence\Manager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;

class RepublicanSilenceAdmin extends AbstractAdmin
{
    /**
     * @var Manager
     */
    private $republicanSilenceManager;

    public function setRepublicanSilenceManager(Manager $manager): void
    {
        $this->republicanSilenceManager = $manager;
    }

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
                    'label' => 'common.begin_at',
                ])
                ->add('finishAt', DateTimePickerType::class, [
                    'label' => 'common.finish_at',
                ])
            ->end()
        ;

        $form->getFormBuilder()->get('zones')->addModelTransformer(new ArrayToStringTransformer());
    }

    public function postPersist($object)
    {
        $this->clearRepublicanSilenceCache($object);
    }

    public function postUpdate($object)
    {
        $this->clearRepublicanSilenceCache($object);
    }

    public function toString($object)
    {
        return implode(', ', $object->getZones());
    }

    private function clearRepublicanSilenceCache(RepublicanSilence $silence): void
    {
        $now = new \DateTime();
        if ($silence->getBeginAt() <= $now && $now < $silence->getFinishAt()) {
            $this->republicanSilenceManager->clearCache($now);
        }
    }
}
