<?php

namespace AppBundle\Security;

use AppBundle\Repository\FailedLoginAttemptRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProvider extends EntityUserProvider
{
    private $failedLoginAttemptRepository;
    private $requestStack;
    private $logger;

    public function __construct(
        ManagerRegistry $registry,
        $classOrAlias,
        $property = null,
        $managerName = null,
        FailedLoginAttemptRepository $failedLoginAttemptRepository,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        parent::__construct($registry, $classOrAlias, $property, $managerName);

        $this->failedLoginAttemptRepository = $failedLoginAttemptRepository;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    public function loadUserByUsername($username)
    {
        $username = mb_strtolower($username);
        $signature = (new FailedLoginAttemptSignature($username, $this->requestStack->getCurrentRequest()->getClientIp()))();

        if (!$this->failedLoginAttemptRepository->canLogin($signature)) {
            $this->logger->warning(sprintf('Max login attempts reached for "%s"', $username), [
                'attempts' => $this->failedLoginAttemptRepository->countAttempts($signature),
                'username' => $username,
                'signature' => $signature,
            ]);

            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        return parent::loadUserByUsername($username);
    }
}
