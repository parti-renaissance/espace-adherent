<?php

namespace App\Security\Voter;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\AdherentMessage\MessageLimiter;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Repository\AdherentMessageRepository;

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

            default:
                $number = $this->repository->countTotalMessage($adherent, $messageType, true);
        }

        return $number < $limit;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::USER_CAN_SEND_MESSAGE === $attribute
            && $subject instanceof AdherentMessageInterface;
    }
}
