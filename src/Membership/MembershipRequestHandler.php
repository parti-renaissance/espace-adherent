<?php

namespace App\Membership;

use App\Address\PostAddressFactory;
use App\Adherent\Unregistration\UnregistrationCommand;
use App\Adherent\UnregistrationHandler;
use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\History\EmailSubscriptionHistoryHandler;
use App\Mailer\MailerService;
use App\Mailer\Message\AdherentAccountActivationMessage;
use App\Mailer\Message\AdherentAccountActivationReminderMessage;
use App\Mailer\Message\AdherentAccountConfirmationMessage;
use App\Mailer\Message\AdherentTerminateMembershipMessage;
use App\OAuth\CallbackManager;
use App\Referent\ReferentTagManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MembershipRequestHandler
{
    private $dispatcher;
    private $adherentFactory;
    private $addressFactory;
    private $callbackManager;
    private $mailer;
    private $manager;
    private $referentTagManager;
    private $membershipRegistrationProcess;
    private $emailSubscriptionHistoryHandler;
    private $unregistrationHandler;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        PostAddressFactory $addressFactory,
        CallbackManager $callbackManager,
        MailerService $mailer,
        ObjectManager $manager,
        ReferentTagManager $referentTagManager,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler,
        UnregistrationHandler $unregistrationHandler
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->addressFactory = $addressFactory;
        $this->dispatcher = $dispatcher;
        $this->callbackManager = $callbackManager;
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->referentTagManager = $referentTagManager;
        $this->membershipRegistrationProcess = $membershipRegistrationProcess;
        $this->emailSubscriptionHistoryHandler = $emailSubscriptionHistoryHandler;
        $this->unregistrationHandler = $unregistrationHandler;
    }

    public function registerAsUser(MembershipRequest $membershipRequest): Adherent
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $this->manager->persist($adherent);

        $this->referentTagManager->assignReferentLocalTags($adherent);

        $this->sendEmailValidation($adherent);

        $this->dispatcher->dispatch(
            UserEvents::USER_CREATED,
            new UserEvent(
                $adherent,
                $membershipRequest->getAllowEmailNotifications(),
                false
            )
        );
        $this->emailSubscriptionHistoryHandler->handleSubscriptions($adherent);

        return $adherent;
    }

    public function sendEmailValidation(Adherent $adherent, bool $isReminder = false): bool
    {
        $token = AdherentActivationToken::generate($adherent);

        $this->manager->persist($token);
        $this->manager->flush();

        $activationUrl = $this->generateMembershipActivationUrl($adherent, $token);

        if ($isReminder) {
            return $this->mailer->sendMessage(AdherentAccountActivationReminderMessage::create($adherent, $activationUrl));
        }

        return $this->mailer->sendMessage(AdherentAccountActivationMessage::create($adherent, $activationUrl));
    }

    public function registerAsAdherent(MembershipRequest $membershipRequest): void
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $this->manager->persist($adherent);

        $this->referentTagManager->assignReferentLocalTags($adherent);

        $this->membershipRegistrationProcess->start($adherent->getUuid()->toString());

        $adherent->join();
        $this->sendEmailValidation($adherent);

        $this->dispatcher->dispatch(
            UserEvents::USER_CREATED,
            new UserEvent(
                $adherent,
                $membershipRequest->getAllowEmailNotifications(),
                $membershipRequest->getAllowMobileNotifications()
            )
        );

        $this->emailSubscriptionHistoryHandler->handleSubscriptions($adherent);

        $this->dispatcher->dispatch(AdherentEvents::REGISTRATION_COMPLETED, new AdherentAccountWasCreatedEvent($adherent, $membershipRequest));
    }

    public function join(Adherent $user, MembershipRequest $membershipRequest): void
    {
        $user->updateMembership($membershipRequest, $this->addressFactory->createFromAddress($membershipRequest->getAddress()));
        $user->join();

        $this->dispatcher->dispatch(UserEvents::USER_SWITCH_TO_ADHERENT, new UserEvent(
            $user,
            $membershipRequest->getAllowEmailNotifications(),
            $membershipRequest->getAllowMobileNotifications()
        ));
        $this->emailSubscriptionHistoryHandler->handleSubscriptions($user);
        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($user);

        $this->manager->flush();

        $this->sendConfirmationJoinMessage($user);

        $this->dispatcher->dispatch(AdherentEvents::REGISTRATION_COMPLETED, new AdherentAccountWasCreatedEvent($user, $membershipRequest));
        $this->dispatcher->dispatch(UserEvents::USER_UPDATED, new UserEvent($user));
    }

    public function sendConfirmationJoinMessage(Adherent $user): void
    {
        $this->mailer->sendMessage(AdherentAccountConfirmationMessage::createFromAdherent($user));
    }

    public function update(Adherent $adherent, MembershipRequest $membershipRequest): void
    {
        $this->dispatcher->dispatch(UserEvents::USER_BEFORE_UPDATE, new UserEvent($adherent));

        $adherent->updateMembership($membershipRequest, $this->addressFactory->createFromAddress($membershipRequest->getAddress()));

        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($adherent);

        $this->dispatcher->dispatch(AdherentEvents::PROFILE_UPDATED, new AdherentProfileWasUpdatedEvent($adherent));

        $this->manager->flush();

        $this->dispatcher->dispatch(UserEvents::USER_UPDATED, new UserEvent($adherent));
    }

    /**
     * /!\ Only relevant for update not for creation.
     */
    private function updateReferentTagsAndSubscriptionHistoryIfNeeded(Adherent $adherent): void
    {
        if ($this->referentTagManager->isUpdateNeeded($adherent)) {
            $oldReferentTags = $adherent->getReferentTags()->toArray();
            $this->referentTagManager->assignReferentLocalTags($adherent);
            $this->emailSubscriptionHistoryHandler->handleReferentTagsUpdate($adherent, $oldReferentTags);
        }
    }

    private function generateMembershipActivationUrl(Adherent $adherent, AdherentActivationToken $token): string
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'activation_token' => (string) $token->getValue(),
        ];

        return $this->callbackManager->generateUrl('app_membership_activate', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function terminateMembership(UnregistrationCommand $command, Adherent $adherent, bool $sendMail = true): void
    {
        $this->unregistrationHandler->handle($adherent, $command);

        if ($sendMail) {
            $message = AdherentTerminateMembershipMessage::createFromAdherent($adherent);
            $this->mailer->sendMessage($message);
        }

        $this->dispatcher->dispatch(UserEvents::USER_DELETED, new UserEvent($adherent));
    }
}
