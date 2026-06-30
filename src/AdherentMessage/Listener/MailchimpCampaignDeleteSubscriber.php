<?php

declare(strict_types=1);

namespace App\AdherentMessage\Listener;

use App\AdherentMessage\Command\AdherentMessageDeleteCommand;
use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(Events::postRemove)]
class MailchimpCampaignDeleteSubscriber
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof MailchimpCampaign && $object->getExternalId()) {
            $this->bus->dispatch(new AdherentMessageDeleteCommand(
                $object->getExternalId(),
                $object->getStaticSegmentId(),
            ));
        }
    }
}
