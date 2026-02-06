<?php

declare(strict_types=1);

namespace App\Admin\Procuration;

use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Entity\Procuration\Request;
use App\Entity\Procuration\RequestSlot;
use App\Form\Admin\Procuration\RequestStatusEnumType;
use App\Procuration\Event\ProcurationEvent;
use App\Procuration\Event\ProcurationEvents;
use App\Utils\PhpConfigurator;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RequestAdmin extends AbstractProcurationAdmin
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

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
                'template' => 'admin/procuration/_list_request_slots.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/procuration/_list_request_status.html.twig',
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

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        $translator = $this->getTranslator();

        return [IteratorCallbackDataSource::CALLBACK => static function (array $procuration) use ($translator) {
            /** @var Request $request */
            $request = $procuration[0];

            try {
                return array_merge(
                    static::getExportCommonFields($request, $translator),
                    [
                        'Statut' => $translator->trans('procuration.request.status.'.$request->status->value),
                        'Associations' => implode(', ', array_map(static function (RequestSlot $slot) {
                            return \sprintf(
                                '%s: %s',
                                $slot->round->name,
                                $slot->proxySlot
                                    ? (string) $slot->proxySlot->proxy
                                    : ($slot->manual ? 'Traité manuellement' : 'En attente')
                            );
                        }, $request->requestSlots->toArray())),
                    ]
                );
            } catch (\Exception $e) {
                return static::getExportErrorFields($request);
            }
        }];
    }
}
