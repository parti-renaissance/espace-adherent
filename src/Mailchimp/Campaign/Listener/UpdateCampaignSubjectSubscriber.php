<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Listener;

use App\Mailchimp\Event\RequestEvent;
use App\Mailchimp\Events;
use App\Scope\ScopeEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCampaignSubjectSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::CAMPAIGN_PRE_EDIT => 'preEdit',
        ];
    }

    public function preEdit(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $message = $event->getMessage();

        switch ($message->getInstanceScope()) {
            case ScopeEnum::DEPUTY:
                $prefix = '[Délégué de circonscription]';
                break;
            case ScopeEnum::SENATOR:
                $prefix = '[Sénateur]';
                break;
            case ScopeEnum::ANIMATOR:
                $prefix = '[Comité]';
                break;
            case ScopeEnum::LEGISLATIVE_CANDIDATE:
            case ScopeEnum::CANDIDATE:
                $prefix = '[Candidat]';
                break;
            case ScopeEnum::CORRESPONDENT:
                $prefix = '[Responsable local]';
                break;
            case ScopeEnum::REGIONAL_COORDINATOR:
                $prefix = '[Coordinateur Régional]';
                break;
            default:
                $prefix = '';
        }

        if ($prefix) {
            $request->setSubject(ltrim(\sprintf('%s %s', $prefix, $message->getSubject())));
        }
    }
}
