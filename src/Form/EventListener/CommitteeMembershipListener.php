<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CommitteeMembershipListener implements EventSubscriberInterface
{
    public function __construct(private readonly CommitteeMembershipManager $committeeMembershipManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onPostSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var Adherent $adherent */
        $adherent = $event->getData();

        if (
            !$adherent instanceof Adherent
            || !($membership = $adherent->getCommitteeMembership())
            || !($committee = $membership->getCommittee())
        ) {
            return;
        }

        $form->get('committee')->setData($committee);
    }

    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var Adherent $adherent */
        $adherent = $form->getData();

        if (!$adherent instanceof Adherent) {
            return;
        }

        $membershipBefore = $adherent->getCommitteeMembership();
        $committeeBefore = $membershipBefore?->getCommittee();

        /** @var Committee|null $committee */
        $committee = $form->get('committee')->getData();

        if ($committee) {
            if ($committeeBefore !== $committee) {
                $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::ADMIN);
            }
        } elseif ($membershipBefore) {
            $this->committeeMembershipManager->unfollowCommittee($membershipBefore);
        }
    }
}
