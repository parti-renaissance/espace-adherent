<?php

namespace App\SmsCampaign\Handler;

use App\Entity\Adherent;
use App\Entity\SmsCampaign\SmsStopHistory;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\SmsCampaign\Command\CatchOvhSmsWebhookCallCommand;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\PhoneNumberUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CatchOvhSmsWebhookCallCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(CatchOvhSmsWebhookCallCommand $command): void
    {
        $payload = $command->payload;

        if (isset($payload['action']) && 'stop' === $payload['action']) {
            if ($this->validate($payload)) {
                $this->entityManager->persist(new SmsStopHistory(
                    new \Datetime($payload['date']),
                    $payload['id'],
                    $payload['receiver']
                ));

                $this->entityManager->flush();
            }

            foreach ($this->entityManager->getRepository(Adherent::class)->findBy(['phone' => PhoneNumberUtils::create($payload['receiver'], 'FR')]) as $adherent) {
                /** @var Adherent $adherent */
                $adherent->removeSubscriptionTypeByCode(SubscriptionTypeEnum::MILITANT_ACTION_SMS);

                $this->entityManager->flush();

                $this->dispatcher->dispatch(new UserEvent($adherent, null, false), UserEvents::USER_UPDATE_SUBSCRIPTIONS);
            }
        }
    }

    private function validate(array $payload): bool
    {
        return 0 === \count($this->entityManager->getRepository(SmsStopHistory::class)->findBy([
            'eventDate' => new \Datetime($payload['date']),
            'campaignExternalId' => $payload['id'],
            'receiver' => $payload['receiver'],
        ]));
    }
}
