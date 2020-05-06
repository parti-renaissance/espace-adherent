<?php

namespace App\Admin;

use App\Entity\RepublicanSilence;
use App\RepublicanSilence\RepublicanSilenceManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;

class RepublicanSilenceAdmin extends AbstractAdmin
{
    /**
     * @var RepublicanSilenceManager
     */
    private $republicanSilenceManager;

    public function setRepublicanSilenceManager(RepublicanSilenceManager $manager): void
    {
        $this->republicanSilenceManager = $manager;
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('referentTags', 'array', ['label' => 'silence_republican.referent_tags.label'])
            ->add('beginAt', null, ['label' => 'common.begin_at'])
            ->add('finishAt', null, ['label' => 'common.finish_at'])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Général', ['class' => 'col-md-6', 'description' => 'silence_republican.help'])
                ->add('referentTags', null, ['label' => 'silence_republican.referent_tags.label'])
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

    public function postPersist($object)
    {
        $this->clearRepublicanSilenceCache();
    }

    public function postUpdate($object)
    {
        $this->clearRepublicanSilenceCache();
    }

    public function postRemove($object)
    {
        $this->clearRepublicanSilenceCache();
    }

    /**
     * @param RepublicanSilence $object
     *
     * @return string
     */
    public function toString($object)
    {
        return implode(', ', $object->getReferentTags()->toArray());
    }

    private function clearRepublicanSilenceCache(): void
    {
        $this->republicanSilenceManager->clearCache(new \DateTime());
    }
}
