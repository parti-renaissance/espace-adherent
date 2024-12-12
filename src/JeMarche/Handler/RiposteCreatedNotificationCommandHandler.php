<?php

namespace App\JeMarche\Handler;

use App\Entity\Jecoute\Riposte;
use App\Firebase\JeMarcheMessaging;
use App\JeMarche\Command\RiposteCreatedNotificationCommand;
use App\JeMarche\Notification\RiposteCreatedNotification;
use App\JeMarche\NotificationTopicBuilder;
use App\Repository\Jecoute\RiposteRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RiposteCreatedNotificationCommandHandler
{
    private $riposteRepository;
    private $messaging;
    private $topicBuilder;

    public function __construct(
        RiposteRepository $riposteRepository,
        JeMarcheMessaging $messaging,
        NotificationTopicBuilder $topicBuilder,
    ) {
        $this->riposteRepository = $riposteRepository;
        $this->messaging = $messaging;
        $this->topicBuilder = $topicBuilder;
    }

    public function __invoke(RiposteCreatedNotificationCommand $command): void
    {
        $riposte = $this->getRiposte($command->getUuid());

        if (!$riposte) {
            return;
        }

        $topic = $this->topicBuilder->buildTopic();

        $this->messaging->send(RiposteCreatedNotification::create($riposte, $topic));
    }

    private function getRiposte(UuidInterface $uuid): ?Riposte
    {
        return $this->riposteRepository->findOneByUuid($uuid);
    }
}
