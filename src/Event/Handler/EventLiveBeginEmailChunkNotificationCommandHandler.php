<?php

namespace App\Event\Handler;

use App\Event\Command\EventLiveBeginEmailChunkNotificationCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceEventNotificationMessage;
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

        $adherents = $this->adherentRepository
            ->createQueryBuilder('a')
            ->select('PARTIAL a.{id, uuid, emailAddress, firstName, lastName}')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $command->chunk)
            ->getQuery()
            ->getResult()
        ;

        if (empty($adherents)) {
            return;
        }

        $this->transactionalMailer->sendMessage(RenaissanceEventNotificationMessage::create(
            $adherents,
            $event->getAuthor(),
            $event,
            $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).'evenements/'.$event->getSlug(),
        ), false);

        $this->cache->set($command->key, true, 900);
    }
}
