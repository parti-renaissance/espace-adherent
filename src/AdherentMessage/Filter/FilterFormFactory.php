<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Exception\InvalidAdherentMessageType;
use AppBundle\Form\AdherentMessageReferentFilterType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FilterFormFactory
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function createForm(AdherentMessageInterface $message, Adherent $user): FormInterface
    {
        switch ($message->getType()) {
            case AdherentMessageTypeEnum::REFERENT:
                return $this->formFactory->create(
                    AdherentMessageReferentFilterType::class,
                    $message->getFilter() ?? new ReferentFilterDataObject($user->getManagedAreaTagCodes())
                );
        }

        throw new InvalidAdherentMessageType($message->getType());
    }
}
