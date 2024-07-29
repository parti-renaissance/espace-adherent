<?php

namespace App\Security;

use App\Repository\FailedLoginAttemptRepository;
use App\Security\Exception\BadCredentialsException;
use App\Security\Exception\MaxLoginAttemptException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider extends EntityUserProvider implements UserProviderInterface
{
    private $failedLoginAttemptRepository;
    private $requestStack;
    private $logger;

    public function __construct(
        ManagerRegistry $registry,
        FailedLoginAttemptRepository $failedLoginAttemptRepository,
        RequestStack $requestStack,
        LoggerInterface $logger,
        string $classOrAlias,
        ?string $property = null,
        ?string $managerName = null
    ) {
        parent::__construct($registry, $classOrAlias, $property, $managerName);

        $this->failedLoginAttemptRepository = $failedLoginAttemptRepository;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $signature = LoginAttemptSignature::createFromRequest($this->requestStack->getMainRequest())->getSignature();

        if (!$this->failedLoginAttemptRepository->canLogin($signature)) {
            $this->logger->warning(\sprintf('Max login attempts reached for "%s"', $identifier), [
                'attempts' => $this->failedLoginAttemptRepository->countAttempts($signature),
                'username' => $identifier,
                'signature' => $signature,
            ]);

            throw new MaxLoginAttemptException();
        }

        try {
            return parent::loadUserByIdentifier($identifier);
        } catch (UserNotFoundException $e) {
            // security.hide_user_not_found option is disabled in order to customize the error message
            // So we must handle that logic ourself
            throw new BadCredentialsException(\sprintf('Username %s not found.', $identifier), 0, $e);
        }
    }
}
