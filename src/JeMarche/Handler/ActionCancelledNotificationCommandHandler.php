<?php

namespace App\JeMarche\Handler;

use App\Entity\Action\Action;
use App\Firebase\JeMarcheMessaging;
use App\JeMarche\Command\ActionCancelledNotificationCommand;
use App\JeMarche\Notification\ActionCancelledNotification;
use App\Repository\Action\ActionRepository;
use App\Repository\PushTokenRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ActionCancelledNotificationCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ActionRepository $actionRepository,
        private readonly JeMarcheMessaging $messaging,
        private readonly PushTokenRepository $pushTokenRepository,
        private readonly string $voxHost,
    ) {
    }

    public function __invoke(ActionCancelledNotificationCommand $command): void
    {
        /** @var Action $action */
        if (!$action = $this->actionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        if ($tokens = $this->pushTokenRepository->findAllForActionInscriptions($action)) {
            $notification = ActionCancelledNotification::create($action, $tokens);
            $notification->setDeepLink('https://'.$this->voxHost.'/actions?uuid='.$action->getUuid()->toString());
            $this->messaging->send($notification);
        }
    }
}
