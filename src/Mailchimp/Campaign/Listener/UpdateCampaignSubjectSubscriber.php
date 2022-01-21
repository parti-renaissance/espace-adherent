<?php

namespace App\Mailchimp\Campaign\Listener;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Mailchimp\Event\RequestEvent;
use App\Mailchimp\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCampaignSubjectSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
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
                $prefix = '[Député]';
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
            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                $prefix = '[Municipales 2020]';
                break;
            case AdherentMessageTypeEnum::REFERENT_ELECTED_REPRESENTATIVE:
            case AdherentMessageTypeEnum::LRE_MANAGER_ELECTED_REPRESENTATIVE:
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
            case AdherentMessageTypeEnum::COALITIONS:
                $prefix = '✊';
                break;
            case AdherentMessageTypeEnum::CORRESPONDENT:
                $prefix = '[Correspondant]';
                break;
            default:
                $prefix = '';
        }

        if ($prefix) {
            $request->setSubject(ltrim(sprintf('%s %s', $prefix, $message->getSubject())));
        }
    }
}
