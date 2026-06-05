<?php

declare(strict_types=1);

namespace App\Routing;

use Symfony\Component\HttpFoundation\Request;

/**
 * Builds the route parameters that keep a redirect on the host the request came from.
 *
 * The host-templated "vox_app_redirect" route ({app_domain}) defaults to the vox host; without an
 * explicit app_domain a campaign user would be bounced off their domain. Passing the current
 * app_domain (the route attribute, falling back to the request host) keeps the redirect sticky.
 */
trait AppDomainParamsTrait
{
    private function appDomainParams(Request $request): array
    {
        return ['app_domain' => $request->attributes->get('app_domain', $request->getHost())];
    }
}
