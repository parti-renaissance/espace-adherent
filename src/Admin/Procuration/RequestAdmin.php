<?php

namespace App\Admin\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Form\Admin\Procuration\RequestStatusEnumType;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RequestAdmin extends AbstractProcurationAdmin
{
    private ?EventDispatcherInterface $eventDispatcher = null;

    protected function configureFormFields(FormMapper $form): void
    {
        parent::configureFormFields($form);

        $form
            ->with('Vote')
                ->add('fromFrance', CheckboxType::class, [
                    'label' => 'procuration.request.fromFrance.label',
                    'required' => false,
                ])
            ->end()
            ->with('Traitement', ['class' => 'col-md-6'])
                ->add('status', RequestStatusEnumType::class, [
                    'label' => 'Statut',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        parent::configureDatagridFilters($filter);

        $filter
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => RequestStatusEnumType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('fromFrance', BooleanFilter::class, [
                'label' => 'procuration.request.fromFrance.label',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list
            ->add('requestSlots', null, [
                'label' => 'Mandataire(s)',
                'template' => 'admin/procuration_v2/_list_request_slots.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/procuration_v2/_list_request_status.html.twig',
            ])
            ->reorder([
                'id',
                'rounds',
                '_fullName',
                'email',
                'phone',
                'adherent',
                'voteZone',
                'requestSlots',
                'status',
                'createdAt',
                ListMapper::NAME_ACTIONS,
            ])
        ;
    }

    /**
     * @param Request $object
     */
    protected function alterObject(object $object): void
    {
        $this->eventDispatcher->dispatch(new ProcurationEvent($object), ProcurationEvents::REQUEST_BEFORE_UPDATE);
    }

    /**
     * @param Request $object
     */
    protected function postUpdate(object $object): void
    {
        $this->eventDispatcher->dispatch(new ProcurationEvent($object), ProcurationEvents::REQUEST_AFTER_UPDATE);
    }

    /** @required */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
