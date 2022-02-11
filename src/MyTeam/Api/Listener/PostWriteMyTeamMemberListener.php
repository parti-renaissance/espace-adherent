<?php

namespace App\MyTeam\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\MyTeam\Member;
use App\MyTeam\DelegatedAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostWriteMyTeamMemberListener implements EventSubscriberInterface
{
    private DelegatedAccessManager $delegatedAccessManager;

    public function __construct(DelegatedAccessManager $delegatedAccessManager)
    {
        $this->delegatedAccessManager = $delegatedAccessManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['updateDelegatedAccess', EventPriorities::POST_WRITE],
        ];
    }

    public function updateDelegatedAccess(ViewEvent $event): void
    {
        $request = $event->getRequest();
        if (Member::class !== $request->get('_api_resource_class')) {
            return;
        }

        // suppression
        if ('delete' === $request->get('_api_item_operation_name')) {
            /** @var Member $member */
            $member = $event->getRequest()->get('data');
            if ($delegatedAccess = $this->delegatedAccessManager->findDelegatedAccess($member)) {
                $this->delegatedAccessManager->removeDelegatedAccess($delegatedAccess);
            }

            return;
        }

        /** @var Member $member */
        $member = $event->getControllerResult();
        if (!$member instanceof Member) {
            return;
        }

        // creation
        if ('post' === $request->get('_api_item_operation_name')
            && $member->getScopeFeatures()) {
            $this->delegatedAccessManager->createDelegatedAccessForMember($member);
        }

        // modification
        $this->delegatedAccessManager->updateDelegatedAccessForMember($member);
    }
}
