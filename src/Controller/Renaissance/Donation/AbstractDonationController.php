<?php

namespace App\Controller\Renaissance\Donation;

use App\Donation\DonationRequest;
use App\Entity\Adherent;
use App\Renaissance\Donation\DonationRequestProcessor;
use App\Renaissance\Donation\DonationRequestStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AbstractDonationController extends AbstractController
{
    private DonationRequestStorage $storage;
    protected DonationRequestProcessor $processor;

    public function __construct(DonationRequestStorage $storage, DonationRequestProcessor $processor)
    {
        $this->storage = $storage;
        $this->processor = $processor;
    }

    protected function getCommand(): DonationRequest
    {
        /** @var Adherent $user */
        $user = $this->getUser();
        $command = $this->storage->getDonationRequest();

        if ($command->getAdherentId()) {
            if (!$user || $user->getId() !== $command->getAdherentId()) {
                $this->storage->clear();

                return new DonationRequest();
            }
        } elseif ($user) {
            $command->updateFromAdherent($user);
        }

        return $command;
    }
}
