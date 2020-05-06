<?php

namespace App\Controller\Api;

use App\Statistics\Acquisition\Aggregator;
use App\Statistics\Acquisition\StatisticsRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AcquisitionStatisticsController extends Controller
{
    /**
     * @Route("/statistics/acquisition", name="api_acquisition_statistics", methods={"GET"})
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
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
