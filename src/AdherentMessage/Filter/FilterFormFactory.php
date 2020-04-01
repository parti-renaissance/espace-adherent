<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Exception\InvalidAdherentMessageType;
use AppBundle\Form\AdherentMessage\AdherentZoneFilterType;
use AppBundle\Form\AdherentMessage\CommitteeFilterType;
use AppBundle\Form\AdherentMessage\MunicipalChiefFilterType;
use AppBundle\Form\AdherentMessage\ReferentFilterType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FilterFormFactory
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function createForm(string $messageType, $data, Adherent $adherent): FormInterface
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::REFERENT:
                return $this->formFactory->create(
                    ReferentFilterType::class,
                    $data,
                    [
                        'single_zone' => 1 === \count($managedArea = $adherent->getManagedAreaTagCodes()),
                        'is_referent_from_paris' => (bool) array_filter(
                            $managedArea,
                            function ($code) { return 0 === strpos($code, '75'); }
                        ),
                        'referent_tags' => $adherent->getManagedArea()->getTags()->toArray(),
                    ]
                );

            case AdherentMessageTypeEnum::DEPUTY:
                return $this->formFactory->create(AdherentZoneFilterType::class, $data, [
                    'referent_tags' => [$adherent->getManagedDistrict()->getReferentTag()],
                ]);

            case AdherentMessageTypeEnum::SENATOR:
                return $this->formFactory->create(AdherentZoneFilterType::class, $data, [
                    'referent_tags' => [$adherent->getSenatorArea()->getDepartmentTag()],
                ]);

            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                return $this->formFactory->create(MunicipalChiefFilterType::class, $data);

            case AdherentMessageTypeEnum::COMMITTEE:
                return $this->formFactory->create(CommitteeFilterType::class, $data);
        }

        throw new InvalidAdherentMessageType(sprintf('Invalid message ("%s") type or data', $messageType));
    }
}
