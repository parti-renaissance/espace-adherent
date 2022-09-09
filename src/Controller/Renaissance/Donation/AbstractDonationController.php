<?php

namespace App\Controller\Renaissance\Donation;

use App\Donation\DonationRequest;
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
        return $this->storage->getDonationRequest();
    }
}
