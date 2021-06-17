<?php

namespace App\JeMarche\Handler;

use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\Poll;
use App\Firebase\JeMarcheMessaging;
use App\JeMarche\Command\PollCreatedNotificationCommand;
use App\JeMarche\Notification\PollCreatedNotification;
use App\JeMarche\NotificationTopicBuilder;
use App\Poll\PollManager;
use App\Repository\Poll\AbstractPollRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PollCreatedNotificationCommandHandler implements MessageHandlerInterface
{
    private $pollRepository;
    private $pollManager;
    private $messaging;
    private $topicBuilder;

    public function __construct(
        AbstractPollRepository $pollRepository,
        PollManager $pollManager,
        JeMarcheMessaging $messaging,
        NotificationTopicBuilder $topicBuilder
    ) {
        $this->pollRepository = $pollRepository;
        $this->pollManager = $pollManager;
        $this->messaging = $messaging;
        $this->topicBuilder = $topicBuilder;
    }

    public function __invoke(PollCreatedNotificationCommand $command): void
    {
        $poll = $this->getPoll($command->getUuid());

        if (!$poll) {
            return;
        }

        if ($poll instanceof LocalPoll) {
            $activePoll = $this->pollManager->findActivePollByZone($poll->getZone());

            if (!$activePoll || !$activePoll->equals($poll)) {
                return;
            }
        }

        $topic = $this->topicBuilder->buildTopic(
            $poll instanceof LocalPoll
                ? $poll->getZone()
                : null
        );

        $this->messaging->send(PollCreatedNotification::create($poll, $topic));
    }

    private function getPoll(UuidInterface $uuid): ?Poll
    {
        return $this->pollRepository->findOneByUuid($uuid);
    }
}
