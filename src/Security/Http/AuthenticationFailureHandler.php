<?php

namespace AppBundle\Security\Http;

use AppBundle\Entity\FailedLoginAttempt;
use AppBundle\Repository\FailedLoginAttemptRepository;
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
        FailedLoginAttemptRepository $failedLoginAttemptRepository,
        array $options = [],
        LoggerInterface $logger = null
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
