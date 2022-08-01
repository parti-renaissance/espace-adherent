<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Renaissance\Membership\MembershipRequestCommandProcessor;
use App\Renaissance\Membership\MembershipRequestCommandStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractAdhesionController extends AbstractController
{
    protected MembershipRequestCommandStorage $storage;
    protected MembershipRequestCommandProcessor $processor;

    public function __construct(MembershipRequestCommandStorage $storage, MembershipRequestCommandProcessor $processor)
    {
        $this->storage = $storage;
        $this->processor = $processor;
    }
}
