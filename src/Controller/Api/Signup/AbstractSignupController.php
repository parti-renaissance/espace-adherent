<?php

declare(strict_types=1);

namespace App\Controller\Api\Signup;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

abstract class AbstractSignupController extends AbstractController
{
    /**
     * Throttles the request by client IP, throwing 429 when the bucket is exhausted.
     *
     * The null-IP fallback funnels proxy-less/CLI traffic into a single shared bucket; this is
     * only safe when a trusted proxy always exposes a real client IP (see TRUSTED_PROXIES).
     */
    protected function enforceIpRateLimit(RateLimiterFactory $limiter, Request $request): void
    {
        if (!$limiter->create($request->getClientIp() ?? 'unknown')->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }
    }
}
