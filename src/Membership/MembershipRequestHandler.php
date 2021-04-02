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
use App\Mailer\Message\AdherentMembershipReminderMessage;
use App\Mailer\Message\AdherentTerminateMembershipMessage;
use App\OAuth\CallbackManager;
use App\Referent\ReferentTagManager;
use App\Referent\ReferentZoneManager;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MembershipRequestHandler
{
    private $dispatcher;
    private $adherentFactory;
    private $addressFactory;
    private $callbackManager;
    private $mailer;
    private $manager;
    private $referentTagManager;
    private $referentZoneManager;
    private $membershipRegistrationProcess;
    private $emailSubscriptionHistoryHandler;
    private $unregistrationHandler;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        PostAddressFactory $addressFactory,
        CallbackManager $callbackManager,
        MailerService $transactionalMailer,
        ObjectManager $manager,
        ReferentTagManager $referentTagManager,
        ReferentZoneManager $referentZoneManager,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler,
        UnregistrationHandler $unregistrationHandler
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->addressFactory = $addressFactory;
        $this->dispatcher = $dispatcher;
        $this->callbackManager = $callbackManager;
        $this->mailer = $transactionalMailer;
        $this->manager = $manager;
        $this->referentTagManager = $referentTagManager;
        $this->referentZoneManager = $referentZoneManager;
        $this->membershipRegistrationProcess = $membershipRegistrationProcess;
        $this->emailSubscriptionHistoryHandler = $emailSubscriptionHistoryHandler;
        $this->unregistrationHandler = $unregistrationHandler;
    }

    public function registerLightUser(LightMembershipRequest $membershipRequest): Adherent
    {
        $adherent = $this->adherentFactory->createFromLightMembershipRequest($membershipRequest);
        $this->manager->persist($adherent);

        $this->referentTagManager->assignReferentLocalTags($adherent);
        $this->referentZoneManager->assignZone($adherent);

        $this->manager->flush();

        $this->dispatcher->dispatch(
            new UserEvent(
                $adherent,
                false,
                false
            ),
            UserEvents::USER_CREATED
        );

        $this->emailSubscriptionHistoryHandler->handleSubscriptions($adherent);

        return $adherent;
    }

    public function registerAsUser(MembershipRequest $membershipRequest): Adherent
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $this->manager->persist($adherent);

        $this->referentTagManager->assignReferentLocalTags($adherent);
        $this->referentZoneManager->assignZone($adherent);

        $this->sendEmailValidation($adherent);

        $this->dispatcher->dispatch(
            new UserEvent(
                $adherent,
                $membershipRequest->getAllowEmailNotifications(),
                false
            ),
            UserEvents::USER_CREATED
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

    public function sendEmailReminder(Adherent $adherent): bool
    {
        $donationUrl = $this->callbackManager->generateUrl('donation_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->mailer->sendMessage(AdherentMembershipReminderMessage::create($adherent, $donationUrl));
    }

    public function registerAsAdherent(MembershipRequest $membershipRequest): void
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $this->manager->persist($adherent);

        $this->referentTagManager->assignReferentLocalTags($adherent);
        $this->referentZoneManager->assignZone($adherent);

        $this->membershipRegistrationProcess->start($adherent->getUuid()->toString());

        $adherent->join();
        $this->sendEmailValidation($adherent);

        $this->dispatcher->dispatch(
            new UserEvent(
                $adherent,
                $membershipRequest->getAllowEmailNotifications(),
                $membershipRequest->getAllowMobileNotifications()
            ),
            UserEvents::USER_CREATED
        );

        $this->emailSubscriptionHistoryHandler->handleSubscriptions($adherent);

        $this->dispatcher->dispatch(new AdherentAccountWasCreatedEvent($adherent, $membershipRequest), AdherentEvents::REGISTRATION_COMPLETED);
    }

    public function join(Adherent $user, MembershipRequest $membershipRequest): void
    {
        $user->updateMembership($membershipRequest, $this->addressFactory->createFromAddress($membershipRequest->getAddress()));
        $user->join();

        $this->dispatcher->dispatch(new UserEvent(
            $user,
            $membershipRequest->getAllowEmailNotifications(),
            $membershipRequest->getAllowMobileNotifications()
        ), UserEvents::USER_SWITCH_TO_ADHERENT);
        $this->emailSubscriptionHistoryHandler->handleSubscriptions($user);
        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($user);

        $this->manager->flush();

        $this->sendConfirmationJoinMessage($user);

        $this->dispatcher->dispatch(new AdherentAccountWasCreatedEvent($user, $membershipRequest), AdherentEvents::REGISTRATION_COMPLETED);
        $this->dispatcher->dispatch(new UserEvent($user), UserEvents::USER_UPDATED);
    }

    public function sendConfirmationJoinMessage(Adherent $user): void
    {
        $this->mailer->sendMessage(AdherentAccountConfirmationMessage::createFromAdherent($user));
    }

    public function update(Adherent $adherent, MembershipRequest $membershipRequest): void
    {
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);

        $adherent->updateMembership($membershipRequest, $this->addressFactory->createFromAddress($membershipRequest->getAddress()));

        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($adherent);

        $this->manager->flush();

        $this->dispatcher->dispatch(new AdherentProfileWasUpdatedEvent($adherent), AdherentEvents::PROFILE_UPDATED);

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATED);
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

        if ($this->referentZoneManager->isUpdateNeeded($adherent)) {
            $this->referentZoneManager->assignZone($adherent);
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

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_DELETED);
    }
}
