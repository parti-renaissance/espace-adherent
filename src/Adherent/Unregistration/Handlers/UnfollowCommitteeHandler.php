<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Committee\CommitteeManager;
use App\Entity\Adherent;
use PhpAmqpLib\Exception\AMQPRuntimeException;

class UnfollowCommitteeHandler implements UnregistrationAdherentHandlerInterface
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    public function supports(Adherent $adherent): bool
    {
        return !$adherent->getMemberships()->isEmpty();
    }

    public function handle(Adherent $adherent): void
    {
        foreach ($adherent->getMemberships() as $membership) {
            try {
                $this->manager->unfollowCommittee($adherent, $membership->getCommittee());
            } catch (AMQPRuntimeException $exception) {
                // catch oldsound rmq timeout exception
            }
        }
    }
}
