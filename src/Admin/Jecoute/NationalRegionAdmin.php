<?php

namespace App\Admin\Jecoute;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region;
use App\Repository\Geo\ZoneRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class NationalRegionAdmin extends AbstractRegionAdmin
{
    protected $baseRoutePattern = 'jecoute-national-region';
    protected $baseRouteName = 'jecoute_national_region';

    /** @var ZoneRepository */
    private $zoneRepository;

    protected function getZoneTypes(): array
    {
        return [Zone::COUNTRY];
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);

        $listMapper
            ->remove('zone.name')
            ->remove('zone.code')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper->remove('zone');

        $formMapper->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function submit(FormEvent $event): void
    {
        /** @var Region $region */
        $region = $event->getData();

        $region->setZone($this->zoneRepository->findOneBy([
            'type' => Zone::COUNTRY,
            'code' => 'FR',
        ]));
    }

    /**
     * @required
     */
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
