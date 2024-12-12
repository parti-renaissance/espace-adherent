<?php

namespace App\Adherent\Handler;

use App\Adherent\Command\UpdateFirebaseTopicsCommand;
use App\Firebase\JeMarcheMessaging;
use App\JeMarche\NotificationTopicBuilder;
use App\Repository\AdherentRepository;
use App\Repository\PushTokenRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateFirebaseTopicsCommandHandler
{
    private AdherentRepository $adherentRepository;
    private JeMarcheMessaging $messaging;
    private PushTokenRepository $pushTokenRepository;
    private NotificationTopicBuilder $notificationTopicBuilder;

    public function __construct(
        AdherentRepository $adherentRepository,
        JeMarcheMessaging $messaging,
        PushTokenRepository $pushTokenRepository,
        NotificationTopicBuilder $notificationTopicBuilder,
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->messaging = $messaging;
        $this->pushTokenRepository = $pushTokenRepository;
        $this->notificationTopicBuilder = $notificationTopicBuilder;
    }

    public function __invoke(UpdateFirebaseTopicsCommand $command): void
    {
        $adherent = $this->adherentRepository->findOneByUuid($command->getUuid());

        if (!$adherent) {
            return;
        }

        $identifiers = $this->pushTokenRepository->findIdentifiersForAdherent($adherent);

        if (empty($identifiers)) {
            return;
        }

        $topics = $this->notificationTopicBuilder->getTopicsFromAdherent($adherent);

        $this->messaging->setTopics($identifiers, $topics);
    }
}
