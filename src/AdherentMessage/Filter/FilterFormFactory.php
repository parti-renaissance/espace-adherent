<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Exception\InvalidAdherentMessageType;
use AppBundle\Form\AdherentMessage\CommitteeFilterType;
use AppBundle\Form\AdherentMessage\ReferentFilterType;
use AppBundle\Form\AdherentMessage\ReferentZoneFilterType;
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
                    return $this->formFactory->create(ReferentFilterType::class, $data);
                }

                return $this->formFactory->create(ReferentZoneFilterType::class, $data);

            case AdherentMessageTypeEnum::COMMITTEE:
                return $this->formFactory->create(CommitteeFilterType::class, $data);
        }

        throw new InvalidAdherentMessageType(sprintf('Invalid message ("%s") type or data', $messageType));
    }
}
