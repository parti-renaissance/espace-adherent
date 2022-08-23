<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Renaissance\Membership\MembershipRequestCommand;
use App\Renaissance\Membership\MembershipRequestCommandProcessor;
use App\Renaissance\Membership\MembershipRequestCommandStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractAdhesionController extends AbstractController
{
    private MembershipRequestCommandStorage $storage;
    protected MembershipRequestCommandProcessor $processor;

    public function __construct(MembershipRequestCommandStorage $storage, MembershipRequestCommandProcessor $processor)
    {
        $this->storage = $storage;
        $this->processor = $processor;
    }

    protected function getCommand(): MembershipRequestCommand
    {
        return $this->storage->getMembershipRequestCommand();
    }
}
