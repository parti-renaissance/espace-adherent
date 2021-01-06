<?php

namespace App\TerritorialCouncil\Convocation;

use App\Address\PostAddressFactory;
use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Convocation;
use App\TerritorialCouncil\Event\ConvocationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Manager
{
    private $entityManager;
    private $eventDispatcher;
    private $postAddressFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PostAddressFactory $addressFactory
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->postAddressFactory = $addressFactory;
    }

    public function create(ConvocationObject $object, Adherent $referent): void
    {
        $convocation = new Convocation();

        $convocation->setCreatedBy($referent);
        $convocation->setTerritorialCouncil($object->getTerritorialCouncil());
        $convocation->setPoliticalCommittee($object->getPoliticalCommittee());
        $convocation->setDescription($object->getDescription());
        $convocation->setMeetingStartDate($object->getMeetingStartDate());
        $convocation->setMeetingEndDate($object->getMeetingEndDate());
        $convocation->setMode($object->getMode());

        if ($object->isOnlineMode()) {
            $convocation->setMeetingUrl($object->getMeetingUrl());
        } else {
            $convocation->updatePostAddress($this->postAddressFactory->createFromAddress($object->getAddress()));
        }

        $this->entityManager->persist($convocation);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ConvocationEvent($convocation), Events::CONVOCATION_CREATED);
    }
}
