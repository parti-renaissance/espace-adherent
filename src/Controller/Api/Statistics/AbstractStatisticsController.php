<?php

namespace App\Controller\Api\Statistics;

use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractStatisticsController extends AbstractController
{
    public function __construct(protected readonly AdherentRepository $adherentRepository)
    {
    }
}
