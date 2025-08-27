<?php

namespace App\Event\Handler;

use App\Entity\Adherent;
use App\Event\Command\EventLiveBeginEmailChunkNotificationCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\EventLiveBeginMessage;
use App\Repository\AdherentRepository;
use App\Repository\Event\EventRepository;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class EventLiveBeginEmailChunkNotificationCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CacheInterface $cache,
    ) {
    }

    public function __invoke(EventLiveBeginEmailChunkNotificationCommand $command): void
    {
        if ($this->cache->has($command->key)) {
            return;
        }

        if (!$event = $this->eventRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        /** @var Adherent[] $adherents */
        $adherents = $this->adherentRepository->findAllByIds($command->chunk, true);

        if (!empty($adherents)) {
            $this->transactionalMailer->sendMessage(EventLiveBeginMessage::create(
                $adherents,
                $event,
                $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).'evenements/'.$event->getSlug(),
            ), false);
        }

        $this->cache->set($command->key, true, 900);
    }
}
