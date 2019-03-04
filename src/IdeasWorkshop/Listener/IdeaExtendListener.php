<?php

namespace AppBundle\IdeasWorkshop\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\IdeasWorkshop\Command\SendMailForExtendedIdeaCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class IdeaExtendListener implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['sendExtendMail', EventPriorities::POST_WRITE],
        ];
    }

    public function sendExtendMail(GetResponseForControllerResultEvent $event): void
    {
        $object = $event->getControllerResult();

        if ($object instanceof Idea && 'extend' === $event->getRequest()->attributes->get('_api_item_operation_name')) {
            $this->bus->dispatch(new SendMailForExtendedIdeaCommand($object));
        }
    }
}
