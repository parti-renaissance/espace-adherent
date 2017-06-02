<?php

namespace AppBundle\Campaign;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class CampaignSilenceSubscriber implements EventSubscriberInterface
{
    private $processor;
    private $twig;

    public function __construct(CampaignSilenceProcessor $processor, \Twig_Environment $twig)
    {
        $this->processor = $processor;
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $request->attributes->set('_campaign_expired', false);

        foreach (['lexik_paybox_', 'sonata_', 'app_admin_', 'admin_app_', '_profiler'] as $prefix) {
            if (0 === strpos($request->attributes->get('_route'), $prefix)) {
                return;
            }
        }

        if ($event->getController()[0] instanceof ExceptionController) {
            return;
        }

        $expired = $this->processor->isCampaignExpired($request);
        $request->attributes->set('_campaign_expired', $expired);

        if (!$expired || $request->attributes->get('_enable_campaign_silence', false)) {
            return;
        }

        $event->setController(function () {
            return new Response($this->twig->render('campaign_silent.html.twig'));
        });
    }
}
