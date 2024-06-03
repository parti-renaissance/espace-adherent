<?php

namespace App\JeMarche\Handler;

use App\Entity\Action\Action;
use App\Firebase\JeMarcheMessaging;
use App\Firebase\Notification\NotificationInterface;
use App\JeMarche\Command\NotifyForActionCommand;
use App\JeMarche\Notification\ActionBeginNotification;
use App\JeMarche\Notification\ActionCancelledNotification;
use App\JeMarche\Notification\ActionCreatedNotification;
use App\JeMarche\Notification\ActionUpdatedNotification;
use App\Repository\Action\ActionRepository;
use App\Repository\PushTokenRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotifyForActionCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ActionRepository $actionRepository,
        private readonly JeMarcheMessaging $messaging,
        private readonly PushTokenRepository $pushTokenRepository,
        private readonly string $voxHost,
    ) {
    }

    public function __invoke(NotifyForActionCommand $command): void
    {
        /** @var Action $action */
        if (!$action = $this->actionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        if ($action->isCancelled()) {
            return;
        }

        if ($tokens = $this->findTokens($action, $command->event)) {
            $notification = $this->createNotification($action, $command->event, $tokens);
            $notification->setDeepLink('https://'.$this->voxHost.'/actions?uuid='.$action->getUuid()->toString());
            $this->messaging->send($notification);
        }
    }

    private function findTokens(Action $action, string $event): array
    {
        if (NotifyForActionCommand::EVENT_CREATE === $event) {
            if (!$zone = $action->getParisBoroughOrDepartment()) {
                return [];
            }

            return $this->pushTokenRepository->findAllForZone($zone);
        }

        return $this->pushTokenRepository->findAllForActionInscriptions($action);
    }

    private function createNotification(Action $action, string $event, array $tokens): NotificationInterface
    {
        switch ($event) {
            case NotifyForActionCommand::EVENT_CREATE:
                return ActionCreatedNotification::create($action, $tokens);
            case NotifyForActionCommand::EVENT_UPDATE:
                return ActionUpdatedNotification::create($action, $tokens);
            case NotifyForActionCommand::EVENT_CANCEL:
                return ActionCancelledNotification::create($action, $tokens);
            case NotifyForActionCommand::EVENT_FIRST_NOTIFICATION:
            case NotifyForActionCommand::EVENT_SECOND_NOTIFICATION:
                return ActionBeginNotification::create($action, $tokens, NotifyForActionCommand::EVENT_FIRST_NOTIFICATION === $event);
        }
    }
}
