<?php

declare(strict_types=1);

namespace App\MyTeam\Listener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\MyTeam\Member;
use App\MyTeam\DelegatedAccessManager;
use App\Scope\FeatureEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MyTeamMemberListener implements EventSubscriberInterface
{
    public function __construct(private readonly DelegatedAccessManager $delegatedAccessManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['updateMemberScopeFeatures', EventPriorities::PRE_WRITE],
                ['updateDelegatedAccess', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function updateMemberScopeFeatures(ViewEvent $event): void
    {
        $request = $event->getRequest();
        if (Member::class !== $request->attributes->get('_api_resource_class')) {
            return;
        }

        /** @var Member $member */
        $member = $event->getControllerResult();
        if (!$member instanceof Member) {
            return;
        }

        if ('_api_/v3/my_team_members/{uuid}_delete' === $request->attributes->get('_api_operation_name')) {
            return;
        }

        $features = $member->getScopeFeatures();
        if (\in_array(FeatureEnum::MY_TEAM, $features, true)) {
            if (!\in_array(FeatureEnum::MY_TEAM_CUSTOM_ROLE, $features, true)) {
                $features[] = FeatureEnum::MY_TEAM_CUSTOM_ROLE;
            }
        } else {
            $key = array_search(FeatureEnum::MY_TEAM_CUSTOM_ROLE, $features, true);
            if (false !== $key) {
                unset($features[$key]);
            }
        }
        $member->setScopeFeatures(array_values($features));
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
