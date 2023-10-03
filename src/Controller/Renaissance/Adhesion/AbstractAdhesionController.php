<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use App\Renaissance\Membership\MembershipRequestProcessor;
use App\Renaissance\Membership\MembershipRequestStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAdhesionController extends AbstractController
{
    protected MembershipRequestStorage $storage;
    protected MembershipRequestProcessor $processor;

    public function __construct(MembershipRequestStorage $storage, MembershipRequestProcessor $processor)
    {
        $this->storage = $storage;
        $this->processor = $processor;
    }

    protected function getCommand(Request $request = null): RenaissanceMembershipRequest
    {
        /** @var ?Adherent $user */
        $user = $this->getUser();
        $command = $this->storage->getMembershipRequest();

        if ($command->getAdherentId()) {
            if (!$user || $user->getId() !== $command->getAdherentId()) {
                $this->storage->clear();

                $command = new RenaissanceMembershipRequest();
            }
        } elseif ($user) {
            $command->updateFromAdherent($user);
        }

        if ($request && $request->query->has(RenaissanceMembershipRequest::UTM_SOURCE)) {
            $command->utmSource = $this->filterUtmParameter((string) $request->query->get(RenaissanceMembershipRequest::UTM_SOURCE));
            $command->utmCampaign = $this->filterUtmParameter((string) $request->query->get(RenaissanceMembershipRequest::UTM_CAMPAIGN));
        }

        if (!$user && $request && $request->query->has(RenaissanceMembershipRequest::EMAIL)) {
            $command->emailFromRequest = true;
            $command->setEmailAddress($request->query->get(RenaissanceMembershipRequest::EMAIL));
        }

        return $command;
    }

    private function filterUtmParameter($utmParameter): ?string
    {
        if (!$utmParameter) {
            return null;
        }

        return mb_substr($utmParameter, 0, 255);
    }
}
