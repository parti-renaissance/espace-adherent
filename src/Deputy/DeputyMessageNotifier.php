<?php

namespace App\Deputy;

use App\Entity\DeputyManagedUsersMessage;
use App\Producer\DeputyMessageDispatcherProducerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DeputyMessageNotifier
{
    private $manager;
    private $producer;

    public function __construct(ObjectManager $manager, DeputyMessageDispatcherProducerInterface $producer)
    {
        $this->manager = $manager;
        $this->producer = $producer;
    }

    public function sendMessage(DeputyMessage $message): void
    {
        $this->manager->persist(DeputyManagedUsersMessage::createFromMessage($message));
        $this->manager->flush();

        $this->producer->scheduleDispatch($message);
    }
}
