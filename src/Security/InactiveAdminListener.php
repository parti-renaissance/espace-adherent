<?php

namespace App\Security;

use App\Entity\Adherent;
use App\Entity\Administrator;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class InactiveAdminListener implements ServiceSubscriberInterface
{
    private $container;
    private $maxIdleTime;

    public function __construct(ContainerInterface $container, int $maxIdleTime = 0)
    {
        $this->container = $container;
        $this->maxIdleTime = $maxIdleTime;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        if ($token = $this->container->get('security.token_storage')->getToken()) {
            $user = $token->getUser();

            $isPreviousAdmin = false;
            foreach ($token->getRoles() as $role) {
                if ($role instanceof Role && 'ROLE_PREVIOUS_ADMIN' == $role->getRole()) {
                    $isPreviousAdmin = true;

                    break;
                }
            }

            if ($this->maxIdleTime > 0 &&
                ($user instanceof Administrator || ($user instanceof Adherent && $isPreviousAdmin))) {
                $lapse = time() - $this->container->get('session')->getMetadataBag()->getLastUsed();

                if ($lapse > $this->maxIdleTime) {
                    $this->container->get('security.token_storage')->setToken(null);

                    $event->setResponse(new RedirectResponse($this->container->get('security.logout_url_generator')->getLogoutPath()));
                }
            }
        }
    }

    public static function getSubscribedServices()
    {
        return [
            'session' => SessionInterface::class,
            'security.token_storage' => TokenStorageInterface::class,
            'security.logout_url_generator' => LogoutUrlGenerator::class,
        ];
    }
}
