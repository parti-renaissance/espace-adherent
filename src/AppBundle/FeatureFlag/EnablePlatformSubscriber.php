<?php

namespace AppBundle\FeatureFlag;

use AppBundle\Controller\AdherentController;
use AppBundle\Controller\CommitteeController;
use AppBundle\Controller\CommitteeEventController;
use AppBundle\Controller\MembershipController;
use AppBundle\Controller\ReferentController;
use AppBundle\Controller\SearchController;
use AppBundle\Controller\Security\AdherentSecurityController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnablePlatformSubscriber implements EventSubscriberInterface
{
    private $enablePlatform;

    public function __construct($enablePlatform)
    {
        $this->enablePlatform = (bool) $enablePlatform;
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
        if ($this->enablePlatform) {
            return;
        }

        foreach (self::$disabledControllers as $disabledController) {
            if ($event->getController()[0] instanceof $disabledController) {
                throw new NotFoundHttpException('The platform is disabled for the moment');
            }
        }
    }

    private static $disabledControllers = [
        AdherentController::class,
        CommitteeController::class,
        MembershipController::class,
        AdherentSecurityController::class,
        CommitteeEventController::class,
        ReferentController::class,
        SearchController::class,
    ];
}
