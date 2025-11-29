<?php

declare(strict_types=1);

namespace App\Admin\Procuration;

use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\ProxySlot;
use App\Form\Admin\Procuration\ProxyStatusEnumType;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Utils\PhpConfigurator;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProxyAdmin extends AbstractProcurationAdmin
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
                ->add('electorNumber', TextType::class, [
                    'label' => 'Numéro d\'électeur',
                    'required' => false,
                ])
            ->end()
            ->with('Traitement', ['class' => 'col-md-6'])
                ->add('status', ProxyStatusEnumType::class, [
                    'label' => 'Statut',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        parent::configureDatagridFilters($filter);

        $filter
            ->add('electorNumber', null, [
                'label' => 'Numéro d\'électeur',
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ProxyStatusEnumType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list
            ->add('proxySlots', null, [
                'label' => 'Mandants',
                'template' => 'admin/procuration_v2/_list_proxy_slots.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/procuration_v2/_list_proxy_status.html.twig',
            ])
            ->reorder([
                'id',
                'rounds',
                '_fullName',
                'email',
                'phone',
                'adherent',
                'voteZone',
                'proxySlots',
                'status',
                'createdAt',
                ListMapper::NAME_ACTIONS,
            ])
        ;
    }

    /**
     * @param Proxy $object
     */
    protected function alterObject(object $object): void
    {
        $this->eventDispatcher->dispatch(new ProcurationEvent($object), ProcurationEvents::PROXY_BEFORE_UPDATE);
    }

    /**
     * @param Proxy $object
     */
    protected function postUpdate(object $object): void
    {
        $this->eventDispatcher->dispatch(new ProcurationEvent($object), ProcurationEvents::PROXY_AFTER_UPDATE);
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        $translator = $this->getTranslator();

        return [IteratorCallbackDataSource::CALLBACK => static function (array $procuration) use ($translator) {
            /** @var Proxy $proxy */
            $proxy = $procuration[0];

            try {
                return array_merge(
                    static::getExportCommonFields($proxy, $translator),
                    [
                        'Statut' => $translator->trans('procuration.proxy.status.'.$proxy->status->value),
                        'Numéro d\'électeur' => $proxy->electorNumber,
                        'Associations' => implode(', ', array_map(static function (ProxySlot $slot) {
                            return \sprintf(
                                '%s: %s',
                                $slot->round->name,
                                $slot->requestSlot
                                    ? (string) $slot->requestSlot->request
                                    : ($slot->manual ? 'Traité manuellement' : 'En attente')
                            );
                        }, $proxy->proxySlots->toArray())),
                    ]
                );
            } catch (\Exception $e) {
                return static::getExportErrorFields($proxy);
            }
        }];
    }
}
