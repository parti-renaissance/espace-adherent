<?php

declare(strict_types=1);

namespace App\Security\Http;

use App\Entity\FailedLoginAttempt;
use App\Repository\FailedLoginAttemptRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    public function __construct(
        private readonly FailedLoginAttemptRepository $failedLoginAttemptRepository,
        HttpKernelInterface $httpKernel,
        HttpUtils $httpUtils,
        array $options = [],
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->failedLoginAttemptRepository->save(FailedLoginAttempt::createFromRequest($request));

        return parent::onAuthenticationFailure($request, $exception);
    }
}
