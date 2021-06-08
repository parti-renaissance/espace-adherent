<?php

namespace App\Device;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Device;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DeviceSubscriber implements EventSubscriberInterface
{
    private $oldPostalCode = null;
    private $deviceManager;

    public function __construct(DeviceManager $deviceManager)
    {
        $this->deviceManager = $deviceManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['preDeserialize', EventPriorities::PRE_DESERIALIZE],
            KernelEvents::VIEW => ['postWrite', EventPriorities::POST_WRITE],
        ];
    }

    public function preDeserialize(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $operationName = $request->attributes->get('_api_item_operation_name');
        $device = $request->get('data');

        if ('put' !== $operationName || !$device instanceof Device) {
            return;
        }

        $this->oldPostalCode = $device->getPostalCode();
    }

    public function postWrite(ViewEvent $event): void
    {
        $device = $event->getControllerResult();

        if (!$event->getRequest()->isMethod(Request::METHOD_PUT) || !$device instanceof Device) {
            return;
        }

        if ($this->oldPostalCode !== $device->getPostalCode()) {
            $this->deviceManager->refreshZoneFromPostalCode($device);
        }
    }
}
