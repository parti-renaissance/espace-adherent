<?php

declare(strict_types=1);

namespace App\Committee\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Committee\Event\BeforeEditCommitteeEvent;
use App\Committee\Event\EditCommitteeEvent;
use App\Entity\Committee;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Scope\ScopeEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class ReplaceAnimatorListener implements EventSubscriberInterface
{
    private ?Committee $committeeBeforeUpdate = null;

    public function __construct(private readonly DelegatedAccessRepository $delegatedAccessRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvent::class => ['onPostWrite', EventPriorities::PRE_WRITE],
            BeforeEditCommitteeEvent::class => 'onBeforeEditCommittee',
            EditCommitteeEvent::class => 'onEditCommittee',
        ];
    }

    public function onBeforeEditCommittee(BeforeEditCommitteeEvent $event): void
    {
        if (!$this->committeeBeforeUpdate) {
            $this->committeeBeforeUpdate = clone $event->getCommittee();
        }
    }

    public function onEditCommittee(EditCommitteeEvent $event): void
    {
        if (null === $this->committeeBeforeUpdate) {
            return;
        }

        $this->replaceAnimator($this->committeeBeforeUpdate, $event->getCommittee());
    }

    public function onPostWrite(ViewEvent $event): void
    {
        $request = $event->getRequest();

        if ('_api_/committees/{uuid}/animator_put' !== $request->attributes->get('_api_operation_name')) {
            return;
        }

        $beforeUpdateCommittee = $request->attributes->get('previous_data');
        $afterUpdateCommittee = $request->attributes->get('data');

        if (
            !$beforeUpdateCommittee instanceof Committee
            || !$afterUpdateCommittee instanceof Committee
        ) {
            return;
        }

        $this->replaceAnimator($beforeUpdateCommittee, $afterUpdateCommittee);
    }

    private function replaceAnimator(Committee $beforeUpdateCommittee, Committee $afterUpdateCommittee): void
    {
        if (!$oldAnimator = $beforeUpdateCommittee->animator) {
            return;
        }

        if ($oldAnimator === $afterUpdateCommittee->animator) {
            return;
        }

        $this->delegatedAccessRepository->removeFromDelegator($oldAnimator, ScopeEnum::ANIMATOR);
    }
}
