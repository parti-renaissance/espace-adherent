<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Symfony\Component\HttpFoundation\Response;

/**
 * This trait is used to redirect the user to the report list when he used action like "moderate"
 */
trait RedirectToReportListRouteTrait
{
    protected function redirectToRoute($route, array $parameters = [], $status = 302): Response
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $reportListRoute = 'admin_app_report_report_list';

        if ($request->query->get('target_name') === $reportListRoute) {
            return parent::redirectToRoute($reportListRoute);
        }

        return parent::redirectToRoute($route, $parameters, $status);
    }
}
