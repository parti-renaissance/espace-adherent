<?php

namespace App\TerritorialCouncil\Designation;

use App\Address\PostAddressFactory;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Entity\TerritorialCouncil\ElectionPoll\PollChoice;
use App\Entity\VotingPlatform\Designation\Designation;
use App\TerritorialCouncil\Event\TerritorialCouncilEvent;
use App\TerritorialCouncil\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateDesignationHandler
{
    private $entityManager;
    private $eventDispatcher;
    private $postAddressFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostAddressFactory $postAddressFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->postAddressFactory = $postAddressFactory;
        $this->eventDispatcher = $dispatcher;
    }

    public function __invoke(UpdateDesignationRequest $request, Election $election): void
    {
        $coTerr = $election->getTerritorialCouncil();

        // persist new designation
        $this->entityManager->persist($newDesignation = $this->createDesignationFromRequest($request, $election));

        $election->setMeetingStartDate($request->getMeetingStartDate());
        $election->setMeetingEndDate($request->getMeetingEndDate());
        $election->setDescription($request->getDescription());
        $election->setQuestions($request->getQuestions());
        $election->setElectionMode($request->getVoteMode());

        if ($election->isOnlineMode()) {
            $election->setMeetingUrl($request->getMeetingUrl());
        } else {
            $election->updatePostAddress($this->postAddressFactory->createFromAddress($request->getAddress()));
        }

        if ($request->isWithPoll()) {
            $poll = new Poll($request->getElectionPollGender());
            foreach ($request->getElectionPollChoices() as $choice) {
                $poll->addChoice(new PollChoice($poll, $choice));
            }
            $election->setElectionPoll($poll);
        }

        $this->entityManager->flush();

        // link new designation with Election and CoTerr
        $election->setDesignation($newDesignation);
        $coTerr->setCurrentDesignation($newDesignation);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(Events::DESIGNATION_SWITCHED, new TerritorialCouncilEvent($coTerr));
    }

    private function createDesignationFromRequest(UpdateDesignationRequest $request, Election $election): Designation
    {
        $coTerr = $election->getTerritorialCouncil();

        $designation = clone $election->getDesignation();

        $designation->setLabel($coTerr->getName());
        $designation->setReferentTags($coTerr->getReferentTags()->toArray());
        $designation->setCandidacyEndDate((clone $request->getVoteStartDate())->modify('-48 hours'));
        $designation->setVoteStartDate($request->getVoteStartDate());
        $designation->setVoteEndDate($request->getVoteEndDate());
        $designation->markAsLimited();

        return $designation;
    }
}
