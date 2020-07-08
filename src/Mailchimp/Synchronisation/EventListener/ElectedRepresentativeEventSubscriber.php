<?php

namespace App\Mailchimp\Synchronisation\EventListener;

use App\ElectedRepresentative\ElectedRepresentativeEvent;
use App\ElectedRepresentative\ElectedRepresentativeEvents;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeArchiveCommand;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommand;
use App\Utils\ArrayUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ElectedRepresentativeEventSubscriber implements EventSubscriberInterface
{
    private $normalizer;
    private $bus;
    private $beforeUpdate;

    public function __construct(NormalizerInterface $normalizer, MessageBusInterface $bus)
    {
        $this->normalizer = $normalizer;
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            ElectedRepresentativeEvents::BEFORE_UPDATE => 'onBeforeUpdate',
            ElectedRepresentativeEvents::POST_UPDATE => 'postUpdate',
        ];
    }

    public function onBeforeUpdate(ElectedRepresentativeEvent $event): void
    {
        $this->beforeUpdate = $this->transformToArray($event->getElectedRepresentative());
    }

    public function postUpdate(ElectedRepresentativeEvent $event): void
    {
        $electedRepresentative = $event->getElectedRepresentative();
        $emailBeforeUpdate = isset($this->beforeUpdate['emailAddress']) ? $this->beforeUpdate['emailAddress'] : null;

        if (!$electedRepresentative->getEmailAddress() && $emailBeforeUpdate) {
            $this->bus->dispatch(new ElectedRepresentativeArchiveCommand($emailBeforeUpdate));

            return;
        }

        $afterUpdate = $this->transformToArray($electedRepresentative);

        $changeFrom = ArrayUtils::arrayDiffRecursive($this->beforeUpdate, $afterUpdate);
        $changeTo = ArrayUtils::arrayDiffRecursive($afterUpdate, $this->beforeUpdate);

        if ($changeFrom || $changeTo) {
            $this->bus->dispatch(new ElectedRepresentativeChangeCommand(
                $electedRepresentative->getUuid(),
                $emailBeforeUpdate ?? $electedRepresentative->getEmailAddress()
            ));
        }
    }

    private function transformToArray(ElectedRepresentative $electedRepresentative): array
    {
        return $this->normalizer->normalize(
            $electedRepresentative,
            'array',
            [
                'groups' => ['elected_representative_change_diff'],
            ]
        );
    }
}
