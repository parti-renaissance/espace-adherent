<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Exception\InvalidAdherentMessageType;
use AppBundle\Form\AdherentMessage\MunicipalChiefFilterType;
use AppBundle\Form\AdherentMessage\ReferentFilterType;
use AppBundle\Intl\FranceCitiesBundle;
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
                    ['single_zone' => 1 === \count($adherent->getManagedAreaTagCodes())]
                );

            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                return $this->formFactory->create(
                    MunicipalChiefFilterType::class,
                    $data,
                    [
                        'city_choices' => array_combine(
                            array_map(
                                static function (string $code) {
                                    return ($data = FranceCitiesBundle::getCityDataFromInseeCode($code)) ? $data['name'] : $code;
                                },
                                $codes = $adherent->getMunicipalChiefManagedArea()->getCodes()
                            ),
                            $codes
                        ),
                    ]
                );
        }

        throw new InvalidAdherentMessageType(sprintf('Invalid message ("%s") type or data', $messageType));
    }
}
