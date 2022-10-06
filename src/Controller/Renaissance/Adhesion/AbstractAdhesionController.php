<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use App\Renaissance\Membership\MembershipRequestProcessor;
use App\Renaissance\Membership\MembershipRequestStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractAdhesionController extends AbstractController
{
    protected MembershipRequestStorage $storage;
    protected MembershipRequestProcessor $processor;

    public function __construct(MembershipRequestStorage $storage, MembershipRequestProcessor $processor)
    {
        $this->storage = $storage;
        $this->processor = $processor;
    }

    protected function getCommand(): RenaissanceMembershipRequest
    {
        /** @var ?Adherent $user */
        $user = $this->getUser();
        $command = $this->storage->getMembershipRequest();

        if ($command->getAdherentId()) {
            if (!$user || $user->getId() !== $command->getAdherentId()) {
                $this->storage->clear();

                return new RenaissanceMembershipRequest();
            }
        } elseif ($user) {
            $command->updateFromAdherent($user);
        }

        return $command;
    }
}
