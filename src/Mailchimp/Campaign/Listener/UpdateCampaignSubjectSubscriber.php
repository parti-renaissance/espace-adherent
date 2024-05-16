<?php

namespace App\Mailchimp\Campaign\Listener;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Mailchimp\Event\RequestEvent;
use App\Mailchimp\Events;
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

        switch ($message->getType()) {
            case AdherentMessageTypeEnum::DEPUTY:
                $prefix = '[Délégué de circonscription]';
                break;
            case AdherentMessageTypeEnum::SENATOR:
                $prefix = '[Sénateur]';
                break;
            case AdherentMessageTypeEnum::COMMITTEE:
                $prefix = '[Comité]';
                break;
            case AdherentMessageTypeEnum::REFERENT:
                $prefix = '[Référent]';
                break;
            case AdherentMessageTypeEnum::REFERENT_ELECTED_REPRESENTATIVE:
                $prefix = '[Élus]';
                break;
            case AdherentMessageTypeEnum::REFERENT_INSTANCES:
                $prefix = '[Conseil territorial]';
                break;
            case AdherentMessageTypeEnum::LEGISLATIVE_CANDIDATE:
                $prefix = '[Candidat aux législatives]';
                break;
            case AdherentMessageTypeEnum::CANDIDATE:
                $prefix = '[Candidat]';
                break;
            case AdherentMessageTypeEnum::CORRESPONDENT:
                $prefix = '[Responsable local]';
                break;
            case AdherentMessageTypeEnum::REGIONAL_COORDINATOR:
                $prefix = '[Coordinateur Régional]';
                break;
            default:
                $prefix = '';
        }

        if ($prefix) {
            $request->setSubject(ltrim(sprintf('%s %s', $prefix, $message->getSubject())));
        }
    }
}
