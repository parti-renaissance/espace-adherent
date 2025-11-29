<?php

declare(strict_types=1);

namespace App\Action\Handler;

use App\Action\RegisterManager;
use App\Entity\Action\Action;
use App\JeMengage\Push\Command\NotifyForActionCommand;
use App\Repository\Action\ActionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RegisterAuthorAsFirstParticipantHandler
{
    public function __construct(
        private readonly RegisterManager $registerManager,
        private readonly ActionRepository $actionRepository,
    ) {
    }

    public function __invoke(NotifyForActionCommand $command): void
    {
        if (NotifyForActionCommand::EVENT_CREATE !== $command->event) {
            return;
        }

        /** @var Action $action */
        if (
            (!$action = $this->actionRepository->findOneByUuid($command->getUuid()))
            || !$action->getAuthor()
        ) {
            return;
        }

        $this->registerManager->register($action, $action->getAuthor());
    }
}
