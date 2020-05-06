<?php

namespace App\Security\Http;

use App\Entity\FailedLoginAttempt;
use App\Repository\FailedLoginAttemptRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    private $failedLoginAttemptRepository;

    public function __construct(
        HttpKernelInterface $httpKernel,
        HttpUtils $httpUtils,
        array $options = [],
        LoggerInterface $logger = null,
        FailedLoginAttemptRepository $failedLoginAttemptRepository
    ) {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);

        $this->failedLoginAttemptRepository = $failedLoginAttemptRepository;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->failedLoginAttemptRepository->save(FailedLoginAttempt::createFromRequest($request));

        return parent::onAuthenticationFailure($request, $exception);
    }
}
