<?php

namespace App\Controller\Api;

use App\Statistics\Acquisition\Aggregator;
use App\Statistics\Acquisition\StatisticsRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AcquisitionStatisticsController extends AbstractController
{
    #[IsGranted('ROLE_OAUTH_SCOPE_READ:STATS')]
    #[Route(path: '/statistics/acquisition', name: 'api_acquisition_statistics', methods: ['GET'])]
    public function getStatsAction(Request $request, Aggregator $aggregator): Response
    {
        try {
            $statisticsRequest = StatisticsRequest::createFromHttpRequest($request);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return $this->json($aggregator->calculate($statisticsRequest));
    }
}
