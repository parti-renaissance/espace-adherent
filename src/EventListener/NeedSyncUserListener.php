<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\EnMarche\NeedSyncUserInterface;
use AppBundle\Membership\AdherentAccountData;
use GuzzleHttp\Client;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NeedSyncUserListener implements EventSubscriberInterface
{
    private $tokenStorage;
    private $authClient;
    private $doctrine;

    public function __construct(TokenStorageInterface $tokenStorage, Client $authClient, RegistryInterface $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authClient = $authClient;
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER => 'updateUserProfile'];
    }

    public function updateUserProfile(FilterControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if (!$controller[0] instanceof NeedSyncUserInterface) {
            return;
        }

        /** @var \AppBundle\Entity\Adherent $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $user->updateAccount($this->getAdherentAccountData());

        $this->doctrine->getManager()->flush();
    }

    private function getAdherentAccountData(): AdherentAccountData
    {
        /** @var \AppBundle\Entity\Adherent $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $result = $this->authClient->request('GET', sprintf('/api/users/%s', $user->getUuid()), ['CONTENT_TYPE' => 'application/json']);

        return $this->getSerializer()->deserialize(
            $result->getBody()->getContents(),
            AdherentAccountData::class,
            'json'
        );
    }

    private function getSerializer(): SerializerInterface
    {
        return SerializerBuilder::create()
            ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
            ->build();
    }
}
