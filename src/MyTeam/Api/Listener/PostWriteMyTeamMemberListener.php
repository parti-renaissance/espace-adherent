<?php

declare(strict_types=1);

namespace App\MyTeam\Api\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
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

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['updateDelegatedAccess', EventPriorities::POST_WRITE],
        ];
    }

    public function updateDelegatedAccess(ViewEvent $event): void
    {
        $request = $event->getRequest();
        if (Member::class !== $request->attributes->get('_api_resource_class')) {
            return;
        }

        // suppression
        if ('_api_/v3/my_team_members/{uuid}_delete' === $request->attributes->get('_api_operation_name')) {
            /** @var Member $member */
            $member = $event->getRequest()->attributes->get('data');
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
        if ('_api_/v3/my_team_members_post' === $request->attributes->get('_api_operation_name') && $member->getScopeFeatures()) {
            $this->delegatedAccessManager->createDelegatedAccessForMember($member);

            return;
        }

        // modification
        $this->delegatedAccessManager->updateDelegatedAccessForMember($member, $request->attributes->get('previous_data'));
    }
}
