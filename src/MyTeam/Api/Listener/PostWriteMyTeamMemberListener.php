<?php

namespace App\MyTeam\Api\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Scope\FeatureEnum;
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
            $delegatedAccess = $this->findDelegatedAccess($member);
            if ($delegatedAccess) {
                $this->entityManager->remove($delegatedAccess);
                $this->entityManager->flush();
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
                $delegatedAccess->setAccesses(array_map(function (string $feature) {
                    return array_flip(FeatureEnum::DELEGATED_ACCESSES_MAPPING)[$feature];
                }, $member->getScopeFeatures()));
                $this->entityManager->flush();

                return;
            }

            $this->entityManager->remove($delegatedAccess);
            $this->entityManager->flush();
        } elseif ($member->getScopeFeatures()) {
            $this->createDelegatedAccess($member);
        }
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
        $delegatedAccess = new DelegatedAccess();
        $delegatedAccess->setDelegator($member->getTeam()->getOwner());
        $delegatedAccess->setDelegated($member->getAdherent());
        $delegatedAccess->setType($member->getTeam()->getScope());
        $delegatedAccess->setRole($member->getRole());
        $delegatedAccess->setAccesses($member->getScopeFeaturesAsAccesses());

        $this->entityManager->persist($delegatedAccess);
        $this->entityManager->flush();
    }
}
