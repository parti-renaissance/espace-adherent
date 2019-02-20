<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Exception\InvalidAdherentMessageType;
use AppBundle\Form\AdherentMessageReferentFilterType;
use AppBundle\Form\AdherentMessageReferentZoneFilterType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FilterFormFactory
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function createForm(string $messageType, $data): FormInterface
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::REFERENT:
                if ($data instanceof ReferentUserFilter) {
                    return $this->formFactory->create(AdherentMessageReferentFilterType::class, $data);
                }
                if ($data instanceof AdherentZoneFilter) {
                    return $this->formFactory->create(AdherentMessageReferentZoneFilterType::class, $data);
                }
        }

        throw new InvalidAdherentMessageType(sprintf('Invalid message ("%s") type or data', $messageType));
    }
}
