<?php

namespace App\OvhCloud;

use App\Entity\SmsCampaign;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Notifier
{
    private $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function sendSmsCampaign(SmsCampaign $campaign, array $phones): ResponseInterface
    {
        return $this->driver->sendSmsBatch($campaign->getContent(), $campaign->getTitle(), $phones);
    }
}
