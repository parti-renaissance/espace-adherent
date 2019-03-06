<?php

namespace AppBundle\Referent;

use AppBundle\Entity\ReferentManagedUsersMessage;
use AppBundle\Producer\ReferentMessageDispatcherProducerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ReferentMessageNotifier
{
    private $manager;
    private $producer;

    public function __construct(ObjectManager $manager, ReferentMessageDispatcherProducerInterface $producer)
    {
        $this->manager = $manager;
        $this->producer = $producer;
    }

    public function sendMessage(ReferentMessage $message): void
    {
        $this->manager->persist(ReferentManagedUsersMessage::createFromMessage($message));
        $this->manager->flush();

        $this->producer->scheduleDispatch($message);
    }
}
