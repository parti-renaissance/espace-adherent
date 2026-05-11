<?php

declare(strict_types=1);

namespace App\Mailchimp;

use App\AdherentMessage\Command\CreatePublicationReachFromEmailCommand;
use App\AdherentMessage\DynamicSegmentInterface;
use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Entity\MailchimpSegment;
use App\Entity\NationalEvent\EventInscription;
use App\Mailchimp\Campaign\CampaignContentRequestBuilder;
use App\Mailchimp\Campaign\CampaignRequestBuilder;
use App\Mailchimp\Campaign\Command\RetrySendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Mailchimp\Contact\SmsOptOutSourceEnum;
use App\Mailchimp\Event\CampaignEvent;
use App\Mailchimp\Event\RequestEvent;
use App\Mailchimp\Exception\FailedSyncException;
use App\Mailchimp\Exception\InvalidCampaignIdException;
use App\Mailchimp\Exception\InvalidContactEmailException;
use App\Mailchimp\Exception\InvalidPayloadException;
use App\Mailchimp\Exception\RemovedContactStatusException;
use App\Mailchimp\Exception\SmsPhoneAlreadySubscribedException;
use App\Mailchimp\MailchimpSegment\SegmentRequestBuilder;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use App\Mailchimp\Synchronisation\Command\AdherentDeleteCommand;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommandInterface;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use App\Mailchimp\Synchronisation\MemberRequest\NewsletterMemberRequestBuilder;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use App\Mailchimp\Synchronisation\RequestBuilder;
use App\Newsletter\NewsletterTypeEnum;
use App\Newsletter\NewsletterValueObject;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\SmsOptOutRepository;
use App\Repository\SubscriptionTypeRepository;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\PhoneNumberUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Manager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const INTEREST_KEY_COMMITTEE_HOST = 'COMMITTEE_HOST';
    public const INTEREST_KEY_COMMITTEE_SUPERVISOR = 'COMMITTEE_SUPERVISOR';
    public const INTEREST_KEY_COMMITTEE_FOLLOWER = 'COMMITTEE_FOLLOWER';
    public const INTEREST_KEY_COMMITTEE_NO_FOLLOWER = 'COMMITTEE_NO_FOLLOWER';
    public const INTEREST_KEY_DEPUTY = 'DEPUTY';
    public const INTEREST_KEY_COORDINATOR = 'COORDINATOR';
    public const INTEREST_KEY_PROCURATION_MANAGER = 'PROCURATION_MANAGER';

    private const string API_CONTACT = 'contact';
    private const string API_MEMBER = 'member';

    public function __construct(
        private readonly Driver $driver,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly MessageBusInterface $bus,
        private readonly ServiceLocator $requestBuildersLocator,
        private readonly SmsOptOutRepository $smsOptOutRepository,
        private readonly SubscriptionTypeRepository $subscriptionTypeRepository,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Creates/updates a Mailchimp member
     */
    public function editMember(Adherent $adherent, AdherentChangeCommandInterface $message): void
    {
        $listId = $this->mailchimpObjectIdMapping->getListIdFromSource($adherent->getSource());

        $memberInfo = $this->driver->getMemberInfo($adherent->getEmailAddress(), $listId);

        if ($adherentStatus = $memberInfo['status']) {
            if (!$adherent->unsubscribeRequestedAt && \in_array($adherentStatus, [ContactStatusEnum::SUBSCRIBED, ContactStatusEnum::UNSUBSCRIBED]) && $adherentStatus !== $adherent->getMailchimpStatus()) {
                $adherent->setEmailUnsubscribed(ContactStatusEnum::UNSUBSCRIBED === $adherentStatus);
            } elseif (ContactStatusEnum::CLEANED === $adherentStatus) {
                $adherent->clean();

                return;
            }
        }

        if (!$adherent->mailchimpContactId && $memberInfo['contact_id']) {
            $adherent->mailchimpContactId = $memberInfo['contact_id'];
        }

        // SMS reconciliation: align local SMS subscription with Mailchimp state before sync
        if ($smsStatus = $memberInfo['sms_subscription_status'] ?? null) {
            if (ContactStatusEnum::SUBSCRIBED === $smsStatus && !$adherent->hasSmsSubscriptionType()) {
                $smsType = $this->subscriptionTypeRepository->findOneByCode(SubscriptionTypeEnum::MILITANT_ACTION_SMS);
                if ($smsType) {
                    $adherent->addSubscriptionType($smsType);
                    $this->logger?->info('[Mailchimp] SMS reconciled: added locally to match Mailchimp', [
                        'adherentUuid' => $adherent->getUuidAsString(),
                    ]);
                }
            } elseif (ContactStatusEnum::UNSUBSCRIBED === $smsStatus) {
                $adherent->removeSubscriptionTypeByCode(SubscriptionTypeEnum::MILITANT_ACTION_SMS);
                if ($adherent->getPhone()) {
                    $this->smsOptOutRepository->add(PhoneNumberUtils::format($adherent->getPhone()), SmsOptOutSourceEnum::Mailchimp);
                }
                $this->logger?->info('[Mailchimp] SMS reconciled: removed locally (carrier opt-out)', [
                    'adherentUuid' => $adherent->getUuidAsString(),
                ]);
            }
        }

        $requestBuilder = $this->requestBuildersLocator
            ->get(RequestBuilder::class)
            ->updateFromAdherent($adherent)
        ;

        $result = false;
        $email = $message->getEmailAddress();
        $adherentUuid = $adherent->getUuidAsString();

        try {
            $contactRequest = $requestBuilder->buildContactRequest($email);

            if ($adherent->mailchimpContactId) {
                $result = $this->driver->updateContact(
                    $adherent->mailchimpContactId,
                    $contactRequest,
                    $listId,
                    true
                );
            } else {
                $contactId = $this->driver->addContact($contactRequest, $listId, true);

                if (null !== $contactId) {
                    $adherent->mailchimpContactId = $contactId;
                    $result = true;
                }
            }

            $adherent->lastMailchimpFailedSyncResponse = null;
            $adherent->mailchimpLastFailedAt = null;
            $adherent->mailchimpSyncEndpoint = self::API_CONTACT;
            $adherent->mailchimpLastSyncedAt = new \DateTimeImmutable();
            // Reset unsubscription request date
            $adherent->unsubscribeRequestedAt = null;
        } catch (InvalidContactEmailException $e) {
            $this->recordSyncFailure($adherent, $e->getMessage());
        } catch (InvalidPayloadException $e) {
            // Fallback to legacy /members endpoint
            try {
                $result = $this->driver->editMember(
                    $requestBuilder->buildMemberRequest($email),
                    $listId,
                    true
                );
                $this->recordSyncFailure($adherent, $e->getMessage());
                $adherent->mailchimpSyncEndpoint = self::API_MEMBER;
                $adherent->mailchimpLastSyncedAt = new \DateTimeImmutable();
            } catch (InvalidContactEmailException|FailedSyncException $fallbackException) {
                $this->recordSyncFailure($adherent, $fallbackException->getMessage());
            } catch (RemovedContactStatusException) {
                $adherent->setEmailUnsubscribed(true);
            }
        } catch (SmsPhoneAlreadySubscribedException $e) {
            try {
                $contactRequestWithoutSms = $requestBuilder->buildContactRequestWithoutSms($email);

                if ($adherent->mailchimpContactId) {
                    $result = $this->driver->updateContact(
                        $adherent->mailchimpContactId,
                        $contactRequestWithoutSms,
                        $listId,
                        true
                    );
                } else {
                    $contactId = $this->driver->addContact($contactRequestWithoutSms, $listId, true);
                    if (null !== $contactId) {
                        $adherent->mailchimpContactId = $contactId;
                        $result = true;
                    }
                }

                if ($result) {
                    $this->recordSyncFailure($adherent, $e->getMessage());
                    $adherent->mailchimpSyncEndpoint = self::API_CONTACT;
                    $adherent->mailchimpLastSyncedAt = new \DateTimeImmutable();
                }
            } catch (\Throwable $retryException) {
                $this->recordSyncFailure($adherent, $retryException->getMessage());
            }
        } catch (RemovedContactStatusException) {
            $adherent->setEmailUnsubscribed(true);
            // Redispatch to re-sync after Mailchimp has processed the unsubscription
            $this->bus->dispatch($message, [new DelayStamp(5000)]);

            return;
        } catch (FailedSyncException $e) {
            $this->recordSyncFailure($adherent, $e->getMessage());
        } catch (\Throwable $e) {
            $this->logger?->error('[Mailchimp] sync failed unexpectedly', ['adherentUuid' => $adherentUuid, 'error' => $e->getMessage(), 'exception' => $e]);
            $this->recordSyncFailure($adherent, $e->getMessage());
        }

        if ($result) {
            $this->updateMemberTags(
                $email,
                $this->mailchimpObjectIdMapping->getMainListId(),
                $requestBuilder
            );
        }
    }

    private function recordSyncFailure(Adherent $adherent, string $response): void
    {
        $adherent->lastMailchimpFailedSyncResponse = $response;
        $adherent->mailchimpLastFailedAt = new \DateTimeImmutable();
    }

    public function editNewsletterMember(NewsletterValueObject $newsletter): void
    {
        $listId = match ($newsletter->getType()) {
            NewsletterTypeEnum::SITE_LEGISLATIVE_CANDIDATE => $this->mailchimpObjectIdMapping->getNewsletterLegislativeCandidateListId(),
            default => $this->mailchimpObjectIdMapping->getNewsletterRenaissanceListId(),
        };

        $requestBuilder = $this->requestBuildersLocator->get(NewsletterMemberRequestBuilder::class);

        $result = $this->driver->editMember(
            $requestBuilder
                ->updateFromValueObject($newsletter)
                ->build($newsletter->getEmail()),
            $listId
        );

        if ($result) {
            $request = $requestBuilder->createMemberTagsRequest($newsletter->getEmail());

            if ($request->hasTags()) {
                // Active/Inactive member's tags
                $this->driver->updateMemberTags($request, $listId);
            }
        }
    }

    public function editElectedRepresentativeMember(
        ElectedRepresentative $electedRepresentative,
        ElectedRepresentativeChangeCommandInterface $message,
    ): void {
        $emailAddress = $electedRepresentative->getEmailAddress();
        $listId = $this->mailchimpObjectIdMapping->getElectedRepresentativeListId();

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator
            ->get(RequestBuilder::class)
            ->updateFromElectedRepresentative($electedRepresentative)
        ;

        $result = $this->driver->editMember(
            $requestBuilder->buildMemberRequest($message->getOldEmailAddress() ?? $emailAddress),
            $listId
        );

        if ($result) {
            $this->updateMemberTags($emailAddress, $listId, $requestBuilder);
        }
    }

    public function editNationalEventInscriptionMember(
        EventInscription $eventInscription,
        NationalEventInscriptionChangeCommand $message,
    ): void {
        $emailAddress = $eventInscription->addressEmail;
        $listId = $this->mailchimpObjectIdMapping->getNationalEventInscriptionListId();

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator
            ->get(RequestBuilder::class)
            ->updateFromNationalEventInscription($eventInscription)
        ;

        $result = $this->driver->editMember(
            $requestBuilder->buildMemberRequest($message->oldEmailAddress ?? $emailAddress),
            $listId
        );

        if ($result) {
            $tagsRequest = new MemberTagsRequest($emailAddress);

            foreach ($this->eventInscriptionRepository->findEventSlugsByEmail($emailAddress) as $slug) {
                $tagsRequest->addTag($slug);
            }

            $this->driver->updateMemberTags($tagsRequest, $listId);
        }
    }

    public function editJecouteContact(JemarcheDataSurvey $dataSurvey, array $zones): void
    {
        $emailAddress = $dataSurvey->getEmailAddress();
        $listId = $this->mailchimpObjectIdMapping->getJecouteListId();

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator
            ->get(RequestBuilder::class)
            ->updateFromDataSurvey($dataSurvey, $zones)
        ;

        $this->driver->editMember(
            $requestBuilder->buildMemberRequest($emailAddress),
            $listId
        );
    }

    public function getCampaignContent(MailchimpCampaign $campaign): string
    {
        if (!$campaign->getExternalId()) {
            throw new InvalidCampaignIdException(\sprintf('Message "%s" does not have a valid campaign id', $campaign->getMessage()->getUuid()));
        }

        return $this->driver->getCampaignContent($campaign->getExternalId());
    }

    public function editCampaign(MailchimpCampaign $campaign): bool
    {
        $message = $campaign->getMessage();

        $this->eventDispatcher->dispatch(new CampaignEvent($campaign), Events::CAMPAIGN_FILTERS_PRE_BUILD);

        /** @var CampaignRequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator->get(CampaignRequestBuilder::class);

        $editCampaignRequest = $requestBuilder->createEditCampaignRequestFromMessage($campaign);

        $this->eventDispatcher->dispatch(new RequestEvent($message, $editCampaignRequest), Events::CAMPAIGN_PRE_EDIT);

        // When ExternalId does not exist, then it is Campaign creation
        if (!$campaignId = $campaign->getExternalId()) {
            $campaignData = $this->driver->createCampaign($editCampaignRequest);

            if (empty($campaignData['id'])) {
                throw new \RuntimeException(\sprintf('Campaign for the message "%s" has not been created', $message->getUuid()));
            }

            $campaign->setExternalId((string) $campaignData['id']);
        } else {
            $campaignData = $this->driver->updateCampaign($campaignId, $editCampaignRequest);
        }

        if (isset($campaignData['recipients']['recipient_count'])) {
            $campaign->setRecipientCount($campaignData['recipients']['recipient_count']);
        }

        return true;
    }

    public function editCampaignContent(MailchimpCampaign $campaign): bool
    {
        $this->checkMessageExternalId($campaign);

        /** @var CampaignContentRequestBuilder $contentRequestBuilder */
        $contentRequestBuilder = $this->requestBuildersLocator->get(CampaignContentRequestBuilder::class);

        $response = $this->driver->editCampaignContent(
            $campaign->getExternalId(),
            $contentRequestBuilder->createContentRequest($message = $campaign->getMessage())
        );

        if (!$this->driver->isSuccessfulResponse($response)) {
            $this->logger->error(
                \sprintf('Campaign content of "%s" message has not been modified', $message->getUuid()->toString()),
                [
                    'message' => $response->getContent(false),
                    'code' => $response->getStatusCode(false),
                ]
            );

            return false;
        }

        return true;
    }

    public function deleteCampaign(string $campaignId): void
    {
        if (!$this->driver->deleteCampaign($campaignId)) {
            $this->logger->error(\sprintf('Campaign "%s" has not be deleted', $campaignId));
        }
    }

    public function sendMailchimpCampaign(MailchimpCampaign $campaign): bool
    {
        $this->checkMessageExternalId($campaign);

        $campaign->markAsSending();

        $this->entityManager->flush();

        $success = $this->driver->sendCampaign($campaign->getExternalId());

        if ($success) {
            $message = $campaign->getMessage();
            $this->bus->dispatch(new CreatePublicationReachFromEmailCommand($message->getUuid()), [new DelayStamp(5000)]);
            $this->bus->dispatch(new SyncReportCommand($message->getUuid(), true, lowPriority: $message->isNational(), delay: 300_000));

            return true;
        }

        $lastError = $this->driver->getLastError();
        $this->logger->error('[Mailchimp] sendCampaign refused — retry scheduled in 30s', [
            'campaign_id' => $campaign->getId(),
            'external_id' => $campaign->getExternalId(),
            'last_error' => $lastError,
        ]);

        $campaign->markAsError($lastError);
        $this->entityManager->flush();

        $this->bus->dispatch(
            new RetrySendMailchimpCampaignCommand($campaign->getId()),
            [new DelayStamp(30_000)]
        );

        return false;
    }

    public function sendTestCampaign(AdherentMessageInterface $message, array $emails): bool
    {
        $campaign = current($message->getMailchimpCampaigns());

        $this->checkMessageExternalId($campaign);

        return $this->driver->sendTestCampaign($campaign->getExternalId(), $emails);
    }

    public function createStaticSegment(string $name, ?string $listId = null, array $emails = []): ?int
    {
        $listId ??= $this->mailchimpObjectIdMapping->getMainListId();
        $response = $this->driver->createStaticSegment($name, $listId, $emails);
        $responseData = $response->toArray();

        if (200 === $response->getStatusCode()) {
            return $responseData['id'] ?? null;
        }

        // Search segment id in existing segments
        if (400 === $response->getStatusCode() && isset($responseData['detail']) && 'Sorry, that tag already exists.' === $responseData['detail']) {
            return $this->findSegmentId($name, $listId);
        }

        return null;
    }

    public function updateStaticSegment(int $segmentId, string $listId, array $emails): bool
    {
        $response = $this->driver->updateStaticSegment($segmentId, $listId, $emails);

        return $this->driver->isSuccessfulResponse($response);
    }

    public function editDynamicSegment(
        DynamicSegmentInterface $segment,
        ?int $segmentId = null,
        ?string $listId = null,
    ): bool {
        /** @var SegmentRequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator->get(SegmentRequestBuilder::class);
        $listId ??= $this->mailchimpObjectIdMapping->getMainListId();

        if ($segmentId) {
            $response = $this->driver->updateDynamicSegment((string) $segmentId, $listId, $requestBuilder->createEditSegmentRequestFromDynamicSegment($segment));
        } else {
            $response = $this->driver->createDynamicSegment($listId, $requestBuilder->createEditSegmentRequestFromDynamicSegment($segment));
        }

        if (200 === $response->getStatusCode()) {
            $responseData = $response->toArray();
            $segment->setMailchimpId($responseData['id']);
            $segment->setRecipientCount($responseData['member_count']);
            $segment->setSynchronized(true);

            return true;
        }

        return false;
    }

    public function findSegmentId(string $name, string $listId): ?int
    {
        $offset = 0;
        $limit = 1000;

        while ($segments = $this->driver->getSegments($listId, $offset, $limit)) {
            foreach ($segments as $segment) {
                if ($segment['name'] === $name) {
                    return $segment['id'];
                }
            }

            $offset += \count($segments);
        }

        return null;
    }

    public function deleteStaticSegment(int $id): bool
    {
        return $this->driver->deleteStaticSegment($id);
    }

    public function addMemberToStaticSegment(int $segmentId, string $mail): void
    {
        $this->driver->pushSegmentMember($segmentId, $mail);
    }

    public function removeMemberFromStaticSegment(int $segmentId, string $mail): void
    {
        $this->driver->deleteSegmentMember($segmentId, $mail);
    }

    public function deleteMember(AdherentDeleteCommand $command): void
    {
        $listId = $this->mailchimpObjectIdMapping->getMainListId();

        // Replace contact email before permanently delete it
        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator->get(RequestBuilder::class);

        $request = $requestBuilder->createReplaceEmailRequest($command->getEmail(), $newEmail = \sprintf(
            'no-reply-mailchimp-contact+deleted-contact%d@en-marche.fr',
            $command->getAdherentId() ?: random_int(1, \PHP_INT_MAX)
        ));

        $emailToDelete = $command->getEmail();
        if ($this->driver->editMember($request, $listId)) {
            $emailToDelete = $newEmail;
        }

        $this->driver->deleteMember($emailToDelete, $listId);
    }

    public function archiveElectedRepresentative(string $mail): void
    {
        $this->driver->archiveMember($mail, $this->mailchimpObjectIdMapping->getElectedRepresentativeListId());
    }

    public function deleteElectedRepresentative(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getElectedRepresentativeListId());
    }

    public function deleteNewsletterMember(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getNewsletterListId());
    }

    public function getReportData(MailchimpCampaign $campaign): array
    {
        $this->checkMessageExternalId($campaign);

        return $this->driver->getReportData($campaign->getExternalId());
    }

    public function getReportOpenData(MailchimpCampaign $campaign, int $offset): array
    {
        $this->checkMessageExternalId($campaign);

        return $this->driver->getReportOpenData($campaign->getExternalId(), $offset);
    }

    public function getReportSentData(MailchimpCampaign $campaign, int $offset): array
    {
        $this->checkMessageExternalId($campaign);

        return $this->driver->getReportSentData($campaign->getExternalId(), $offset);
    }

    public function getCampaignStatus(MailchimpCampaign $campaign): ?MailchimpStatusEnum
    {
        $this->checkMessageExternalId($campaign);

        $status = $this->driver->getCampaignStatus($campaign->getExternalId());

        return $status ? MailchimpStatusEnum::tryFrom($status) : null;
    }

    public function getReportClickData(MailchimpCampaign $campaign, int $offset): array
    {
        $this->checkMessageExternalId($campaign);

        return $this->driver->getReportClickData($campaign->getExternalId(), $offset);
    }

    private function checkMessageExternalId(MailchimpCampaign $campaign): void
    {
        if (!$campaign->getExternalId()) {
            throw new InvalidCampaignIdException(\sprintf('Message "%s" does not have a valid campaign id', $campaign->getMessage()->getUuid()->toString()));
        }
    }

    private function updateMemberTags(string $emailAddress, string $listId, RequestBuilder $requestBuilder): void
    {
        // Only push the explicit active/inactive sets — do NOT GET current tags and mark them all
        // false, otherwise we wipe tags managed outside RequestBuilder (national_event:*, static
        // labels, etc.) on every sync. The RequestBuilder already declares which managed tags need
        // to be deactivated based on the adherent state via getInactiveTags().
        $this->driver->updateMemberTags(
            $requestBuilder->createMemberTagsRequest($emailAddress),
            $listId
        );
    }

    public function createMailchimpSegment(MailchimpSegment $mailchimpSegment): ?int
    {
        $listId = MailchimpSegment::LIST_MAIN === $mailchimpSegment->getList()
            ? $this->mailchimpObjectIdMapping->getMainListId()
            : $this->mailchimpObjectIdMapping->getElectedRepresentativeListId();

        return $this->createStaticSegment($mailchimpSegment->getLabel(), $listId);
    }

    public function retrySendCampaign(MailchimpCampaign $campaign): bool
    {
        if ($status = $this->getCampaignStatus($campaign)) {
            if (MailchimpStatusEnum::Sent === $status || MailchimpStatusEnum::Sending === $status) {
                $campaign->status = $status;

                return true;
            }
        }

        return $this->sendMailchimpCampaign($campaign);
    }
}
