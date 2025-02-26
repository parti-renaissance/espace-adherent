<?php

namespace App\Adherent\Referral\Subscriber;

use App\Adherent\Referral\Notifier;
use App\Adherent\Referral\StatusEnum;
use App\Adhesion\Events\NewCotisationEvent;
use App\Entity\Referral;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\ReferralRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdhesionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ReferralRepository $referralRepository,
        private readonly EntityManagerInterface $em,
        private readonly Notifier $notifier,
    ) {
    }

    public function updateReferrals(UserEvent $event): void
    {
        $adherent = $event->getAdherent();

        /** @var Referral[] $referrals */
        $referrals = $this->referralRepository->findBy(['emailAddress' => $adherent->getEmailAddress()]);

        foreach ($referrals as $referral) {
            if (
                !\in_array($referral->status, [
                    StatusEnum::INVITATION_SENT,
                    StatusEnum::ACCOUNT_CREATED,
                ])
            ) {
                continue;
            }

            if ($adherent->isRenaissanceAdherent()) {
                $referral->status = StatusEnum::ADHESION_FINISHED;

                $this->notifier->sendAdhesionFinishedMessage($referral);

                continue;
            }

            $referral->status = StatusEnum::ACCOUNT_CREATED;
        }

        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_CREATED => ['updateReferrals', -257],
            NewCotisationEvent::class => ['updateReferrals', -257],
        ];
    }
}
