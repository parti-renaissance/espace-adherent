<?php

namespace App\Form\EventListener;

use App\Entity\EntityContentInterface;
use League\CommonMark\CommonMarkConverter;
use Lullabot\AMP\AMP;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AmpSynchronisedListener implements EventSubscriberInterface
{
    protected $markdown;

    public function __construct(CommonMarkConverter $markdown)
    {
        $this->markdown = $markdown;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'setAmpContent',
        ];
    }

    public function setAmpContent(FormEvent $event): void
    {
        $entityContent = $event->getData();

        if (!$entityContent instanceof EntityContentInterface) {
            return;
        }

        $html = $this->markdown->convertToHtml($entityContent->getContent());

        $amp = new AMP();
        $amp->loadHtml($html);

        $entityContent->setAmpContent($amp->convertToAmpHtml());
    }
}
