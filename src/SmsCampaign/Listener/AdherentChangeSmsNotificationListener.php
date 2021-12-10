<?php

namespace App\SmsCampaign\Listener;

use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\OvhCloud\Driver;
use App\Utils\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdherentChangeSmsNotificationListener implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private bool $smsEnabled = true;
    private Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_BEFORE_UPDATE => 'beforeUpdate',
            UserEvents::USER_UPDATE_SUBSCRIPTIONS => 'onSubscriptionsUpdate',
        ];
    }

    public function beforeUpdate(UserEvent $event): void
    {
        $this->smsEnabled = $event->getUser()->hasSmsSubscriptionType();
    }

    public function onSubscriptionsUpdate(UserEvent $event): void
    {
        $adherent = $event->getUser();
        if (!$this->smsEnabled && $adherent->hasSmsSubscriptionType() && $adherent->getPhone()) {
            $phone = PhoneNumberUtils::format($adherent->getPhone(), PhoneNumberFormat::E164);

            try {
                $this->driver->resubscribeContact($phone);
            } catch (\Throwable $e) {
                // Avoid to log 404 errors
                if ($this->logger && 404 !== $e->getCode()) {
                    $this->logger->error(sprintf('[OVH] resubscribe "%s" failed', $phone), ['exception' => $e]);
                }
            }
        }
    }
}
