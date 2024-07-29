<?php

namespace App\AdherentMessage\Filter;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Exception\InvalidAdherentMessageType;
use App\Form\AdherentMessage\AdherentGeoZoneFilterType;
use App\Form\AdherentMessage\AdvancedMessageFilterType;
use App\Form\AdherentMessage\JecouteFilterType;
use App\Form\AdherentMessage\SimpleMessageFilterType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FilterFormFactory
{
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function createForm(string $messageType, $data, Adherent $adherent): FormInterface
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::DEPUTY:
                return $this->formFactory->create(AdvancedMessageFilterType::class, $data, [
                    'message_type' => $messageType,
                    'zones' => [$adherent->getDeputyZone()],
                ]);

            case AdherentMessageTypeEnum::COMMITTEE:
                return $this->formFactory->create(SimpleMessageFilterType::class, $data);

            case AdherentMessageTypeEnum::CANDIDATE:
                return $this->formFactory->create(AdherentGeoZoneFilterType::class, $data, [
                    'space_type' => AdherentMessageTypeEnum::CANDIDATE,
                ]);

            case AdherentMessageTypeEnum::CANDIDATE_JECOUTE:
                return $this->formFactory->create(JecouteFilterType::class, $data, [
                    'space_type' => AdherentMessageTypeEnum::CANDIDATE_JECOUTE,
                ]);
        }

        throw new InvalidAdherentMessageType(\sprintf('Invalid message ("%s") type or data', $messageType));
    }
}
