<?php

namespace App\Security\Listener;

use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Override Sensio ExtraFrameworkBundle SecurityListener
 * and use initial CONTROLLER kernel event instead of CONTROLLER_ARGUMENTS
 */
class SecurityListener implements EventSubscriberInterface
{
    private $tokenStorage;
    private $authChecker;
    private $language;
    private $trustResolver;
    private $roleHierarchy;

    public function __construct(
        ExpressionLanguage $language,
        AuthenticationTrustResolverInterface $trustResolver,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker = null,
        RoleHierarchyInterface $roleHierarchy = null
    ) {
        $this->language = $language;
        $this->trustResolver = $trustResolver;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$configurations = $request->attributes->get('_security')) {
            return;
        }

        if (null === $this->tokenStorage->getToken()) {
            throw new AccessDeniedException('No user token or you forgot to put your controller behind a firewall while using a @Security tag.');
        }

        foreach ($configurations as $configuration) {
            if (!$this->language->evaluate($configuration->getExpression(), $this->getVariables($event))) {
                if ($statusCode = $configuration->getStatusCode()) {
                    throw new HttpException($statusCode, $configuration->getMessage());
                }

                throw new AccessDeniedException($configuration->getMessage() ?: sprintf('Expression "%s" denied access.', $configuration->getExpression()));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER => ['onKernelController', -2]];
    }

    private function getVariables(KernelEvent $event)
    {
        $request = $event->getRequest();
        $token = $this->tokenStorage->getToken();
        $variables = [
            'token' => $token,
            'user' => $token->getUser(),
            'object' => $request,
            'subject' => $request,
            'request' => $request,
            'roles' => $this->getRoles($token),
            'trust_resolver' => $this->trustResolver,
            // needed for the is_granted expression function
            'auth_checker' => $this->authChecker,
        ];

        if ($diff = array_intersect(array_keys($variables), array_keys($request->attributes->all()))) {
            $singular = 1 === \count($diff);
            throw new \RuntimeException(sprintf('Request attribute%s "%s" cannot be defined as %s collide%s with built-in security expression variables.', $singular ? '' : 's', implode('", "', $diff), $singular ? 'it' : 'they', $singular ? 's' : ''));
        }

        // controller variables should also be accessible
        return array_merge($request->attributes->all(), $variables);
    }

    private function getRoles(TokenInterface $token): array
    {
        if (method_exists($this->roleHierarchy, 'getReachableRoleNames')) {
            if (null !== $this->roleHierarchy) {
                $roles = $this->roleHierarchy->getReachableRoleNames($token->getRoleNames());
            } else {
                $roles = $token->getRoleNames();
            }
        } else {
            if (null !== $this->roleHierarchy) {
                $roles = $this->roleHierarchy->getReachableRoles($token->getRoles());
            } else {
                $roles = $token->getRoles();
            }

            $roles = array_map(function ($role) {
                return $role->getRole();
            }, $roles);
        }

        return $roles;
    }
}
