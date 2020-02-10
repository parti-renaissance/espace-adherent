<?php

namespace AppBundle\Security\Voter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\MessageLimiter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Repository\AdherentMessageRepository;

class AdherentMessageLimitVoter extends AbstractAdherentVoter
{
    public const USER_CAN_SEND_MESSAGE = 'USER_CAN_SEND_MESSAGE';

    private $limiter;
    private $repository;

    public function __construct(MessageLimiter $limiter, AdherentMessageRepository $repository)
    {
        $this->limiter = $limiter;
        $this->repository = $repository;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$this->limiter->support($messageType = $subject->getType())) {
            return self::ACCESS_GRANTED;
        }

        /** @var AdherentMessageInterface $subject */
        $limit = $this->limiter->getLimit($messageType);

        switch ($messageType) {
            case AdherentMessageTypeEnum::COMMITTEE:
                $number = $this->repository->countTotalCommitteeMessage(
                    $adherent,
                    $subject->getFilter()->getCommittee(),
                    true
                );
                break;

            case AdherentMessageTypeEnum::CITIZEN_PROJECT:
                $number = $this->repository->countTotalCitizenProjectMessage(
                    $adherent,
                    $subject->getFilter()->getCitizenProject(),
                    true
                );
                break;

            default:
                $number = $this->repository->countTotalMessage($adherent, $messageType, true);
        }

        return $number < $limit;
    }

    protected function supports($attribute, $subject)
    {
        return self::USER_CAN_SEND_MESSAGE === $attribute
            && $subject instanceof AdherentMessageInterface
        ;
    }
}
