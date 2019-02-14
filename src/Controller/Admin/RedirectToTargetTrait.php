<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This trait is used to redirect the user to the report list when he used action like "moderate"
 */
trait RedirectToTargetTrait
{
    protected function prepareRedirectFromRequest(Request $request): ?Response
    {
        if ($request->query->has('target_route')) {
            return $this->redirectToRoute($request->query->get('target_route'));
        }

        return null;
    }
}
