<?php

namespace App\MyTeam\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\MyTeam\RoleEnum;
use App\Repository\MyTeam\DelegatedAccessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PostWriteMyTeamMemberListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private DelegatedAccessRepository $delegatedAccessRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DelegatedAccessRepository $delegatedAccessRepository
    ) {
        $this->entityManager = $entityManager;
        $this->delegatedAccessRepository = $delegatedAccessRepository;
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
            if ($delegatedAccess = $this->findDelegatedAccess($member)) {
                $this->removeDelegatedAccess($delegatedAccess);
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
            $this->createDelegatedAccess($member);
        }

        // modification
        $delegatedAccess = $this->findDelegatedAccess($member);
        if ($delegatedAccess) {
            if ($member->getScopeFeatures()) {
                $delegatedAccess->setScopeFeatures($member->getScopeFeatures());
                $this->entityManager->flush();

                return;
            }

            $this->removeDelegatedAccess($delegatedAccess);
        } elseif ($member->getScopeFeatures()) {
            $this->createDelegatedAccess($member);
        }
    }

    private function removeDelegatedAccess(DelegatedAccess $delegatedAccess): void
    {
        if (0 === \count($delegatedAccess->getAccesses())) {
            $this->entityManager->remove($delegatedAccess);
        } else {
            $delegatedAccess->setScopeFeatures([]);
        }

        $this->entityManager->flush();
    }

    private function findDelegatedAccess(Member $member): ?DelegatedAccess
    {
        $team = $member->getTeam();

        return $this->delegatedAccessRepository->findOneBy([
            'delegated' => $member->getAdherent(),
            'delegator' => $team->getOwner(),
            'type' => $team->getScope(),
        ]);
    }

    private function createDelegatedAccess(Member $member): void
    {
        $delegatedAccess = $this->findDelegatedAccess($member);
        if ($delegatedAccess) {
            $delegatedAccess->setScopeFeatures($member->setScopeFeatures());

            return;
        }

        $delegatedAccess = new DelegatedAccess();
        $delegatedAccess->setDelegator($member->getTeam()->getOwner());
        $delegatedAccess->setDelegated($member->getAdherent());
        $delegatedAccess->setType($member->getTeam()->getScope());
        $delegatedAccess->setRole(RoleEnum::LABELS[$member->getRole()]);
        $delegatedAccess->setScopeFeatures($member->getScopeFeatures());

        $this->entityManager->persist($delegatedAccess);
        $this->entityManager->flush();
    }
}
