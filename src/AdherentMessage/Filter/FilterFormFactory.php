<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Exception\InvalidAdherentMessageType;
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
                    ]
                );

            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                return $this->formFactory->create(MunicipalChiefFilterType::class, $data);
        }

        throw new InvalidAdherentMessageType(sprintf('Invalid message ("%s") type or data', $messageType));
    }
}
