<?php

namespace App\Admin\Jecoute;

use App\Address\AddressInterface;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\RegionRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Service\Attribute\Required;

class NationalRegionAdmin extends AbstractRegionAdmin
{
    protected $baseRoutePattern = 'jecoute-national-region';
    protected $baseRouteName = 'jecoute_national_region';

    private ZoneRepository $zoneRepository;
    private RegionRepository $regionRepository;

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $list = parent::configureActionButtons($buttonList, $action, $object);

        if ($this->regionRepository->hasNationalCampaign()) {
            unset($list['create']);
        }

        return $list;
    }

    protected function getZoneTypes(): array
    {
        return [Zone::COUNTRY];
    }

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list
            ->remove('zone.name')
            ->remove('zone.code')
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        parent::configureFormFields($form);

        $form->remove('zone');

        $form->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function submit(FormEvent $event): void
    {
        /** @var Region $region */
        $region = $event->getData();

        $region->setZone($this->zoneRepository->findOneBy([
            'type' => Zone::COUNTRY,
            'code' => AddressInterface::FRANCE,
        ]));
    }

    #[Required]
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }

    #[Required]
    public function setRegionRepository(RegionRepository $regionRepository): void
    {
        $this->regionRepository = $regionRepository;
    }
}
