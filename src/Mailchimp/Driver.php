<?php

declare(strict_types=1);

namespace App\Mailchimp;

use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Mailchimp\Campaign\Request\EditCampaignRequest;
use App\Mailchimp\Exception\FailedSyncException;
use App\Mailchimp\Exception\InvalidContactEmailException;
use App\Mailchimp\Exception\InvalidPayloadException;
use App\Mailchimp\Exception\RemovedContactStatusException;
use App\Mailchimp\Exception\SmsPhoneAlreadySubscribedException;
use App\Mailchimp\MailchimpSegment\Request\EditSegmentRequest;
use App\Mailchimp\Synchronisation\Request\ContactRequest;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class Driver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private HttpClientInterface $client;
    private string $listId;
    private ?ResponseInterface $lastResponse = null;
    private bool $debug;

    public function __construct(
        HttpClientInterface $mailchimpClient,
        string $listId,
        bool $mailchimpDebug = false,
    ) {
        $this->client = $mailchimpClient;
        $this->listId = $listId;
        $this->debug = $mailchimpDebug;
    }

    /**
     * Create or update a member (legacy /members endpoint)
     */
    public function editMember(MemberRequest $request, string $listId, bool $throw = false): bool
    {
        $response = $this->send(
            'PUT',
            \sprintf('/lists/%s/members/%s', $listId, $this->createHash($request->getMemberIdentifier())),
            $request->toArray()
        );

        if ($this->isSuccessfulResponse($response)) {
            return true;
        }

        if ($throw) {
            $this->throwSyncException($this->readErrorBody($response));
        }

        return false;
    }

    public function addContact(ContactRequest $request, string $listId, bool $throw = false): ?string
    {
        $response = $this->send(
            'POST',
            \sprintf('/audiences/%s/contacts', $listId),
            $request->toArray(),
        );

        if ($this->isSuccessfulResponse($response)) {
            return $response->toArray()['id'] ?? null;
        }

        if ($throw) {
            $this->throwContactSyncException($this->readErrorBody($response), $request->getPhone());
        }

        return null;
    }

    public function updateContact(string $contactId, ContactRequest $request, string $listId, bool $throw = false): bool
    {
        $response = $this->send(
            'PATCH',
            \sprintf('/audiences/%s/contacts/%s', $listId, $contactId),
            $request->toArray()
        );

        if ($this->isSuccessfulResponse($response)) {
            return true;
        }

        if ($throw) {
            $this->throwContactSyncException($this->readErrorBody($response), $request->getPhone());
        }

        return false;
    }

    public function getMemberTags(string $mail, string $listId): array
    {
        $response = $this->send('GET', \sprintf('/lists/%s/members/%s/tags', $listId, $this->createHash($mail)));

        if (!$this->isSuccessfulResponse($response)) {
            return [];
        }

        return array_column($response->toArray()['tags'] ?? [], 'name');
    }

    public function updateMemberTags(MemberTagsRequest $request, string $listId): bool
    {
        return $this->sendRequest(
            'POST',
            \sprintf('/lists/%s/members/%s/tags', $listId, $this->createHash($request->getMemberIdentifier())),
            $request->toArray()
        );
    }

    public function getCampaignContent(string $campaignId): string
    {
        $response = $this->send('GET', \sprintf('/campaigns/%s/content', $campaignId));

        if ($this->isSuccessfulResponse($response)) {
            return $response->toArray()['html'] ?? '';
        }

        $this->logger->error(\sprintf('[API] Error: %s', $response->getContent(false)), ['campaignId' => $campaignId]);

        return '';
    }

    public function getCampaignStatus(string $campaignId): ?string
    {
        $response = $this->send('GET', \sprintf('/campaigns/%s?fields=status', $campaignId));

        return $this->isSuccessfulResponse($response) ? ($response->toArray()['status'] ?? null) : null;
    }

    public function getCampaignSavedSegmentId(string $campaignId): ?int
    {
        $response = $this->send('GET', \sprintf('/campaigns/%s?fields=recipients.segment_opts.saved_segment_id', $campaignId));

        if (!$this->isSuccessfulResponse($response)) {
            return null;
        }

        $segmentId = $response->toArray()['recipients']['segment_opts']['saved_segment_id'] ?? null;

        return null === $segmentId ? null : (int) $segmentId;
    }

    public function createCampaign(EditCampaignRequest $request): array
    {
        $response = $this->send('POST', '/campaigns', $request->toArray());

        return $this->isSuccessfulResponse($response, true) ? $response->toArray() : [];
    }

    public function updateCampaign(string $campaignId, EditCampaignRequest $request): array
    {
        $response = $this->send('PATCH', \sprintf('/campaigns/%s', $campaignId), $request->toArray());

        return $this->isSuccessfulResponse($response, true) ? $response->toArray() : [];
    }

    public function editCampaignContent(string $campaignId, EditCampaignContentRequest $request): ResponseInterface
    {
        return $this->send('PUT', \sprintf('/campaigns/%s/content', $campaignId), $request->toArray());
    }

    public function deleteCampaign(string $campaignId): bool
    {
        return $this->sendRequest('DELETE', \sprintf('/campaigns/%s', $campaignId));
    }

    public function sendCampaign(string $externalId): bool
    {
        return $this->sendRequest('POST', \sprintf('/campaigns/%s/actions/send', $externalId), [], true);
    }

    public function sendTestCampaign(string $externalId, array $emails): bool
    {
        return $this->sendRequest('POST', \sprintf('/campaigns/%s/actions/test', $externalId), [
            'test_emails' => $emails,
            'send_type' => 'html',
        ]);
    }

    public function getSegments(string $listId, int $offset = 0, int $limit = 1000): array
    {
        $params = [
            'offset' => $offset,
            'count' => $limit,
            'fields' => 'segments.id,segments.name',
        ];

        $response = $this->send('GET', \sprintf('/lists/%s/segments?%s', $listId, http_build_query($params)));

        return $this->isSuccessfulResponse($response) ? $response->toArray()['segments'] : [];
    }

    /**
     * Reads a single segment with its effective member_count (used after
     * preparation to verify how many emails Mailchimp actually accepted).
     *
     * @return array{id?: int, name?: string, member_count?: int, ...}
     */
    public function getSegment(int $segmentId, string $listId): array
    {
        $response = $this->send('GET', \sprintf('/lists/%s/segments/%d', $listId, $segmentId));

        return $this->isSuccessfulResponse($response) ? $response->toArray() : [];
    }

    public function getMembers(string $listId, int $offset = 0, int $limit = 1000): array
    {
        $params = [
            'offset' => $offset,
            'count' => $limit,
            'fields' => 'members.email_address,members.contact_id',
        ];

        $response = $this->send('GET', \sprintf('/lists/%s/members?%s', $listId, http_build_query($params)));

        return $this->isSuccessfulResponse($response) ? $response->toArray()['members'] : [];
    }

    public function createStaticSegment(string $name, string $listId, array $emails = []): ResponseInterface
    {
        return $this->send('POST', \sprintf('/lists/%s/segments', $listId), [
            'name' => $name,
            'static_segment' => $emails,
        ]);
    }

    public function updateStaticSegment(int $segmentId, string $listId, array $emails): ResponseInterface
    {
        return $this->send('PATCH', \sprintf('/lists/%s/segments/%d', $listId, $segmentId), [
            'static_segment' => $emails,
        ]);
    }

    public function deleteStaticSegment(int $id): bool
    {
        return $this->sendRequest('DELETE', \sprintf('/lists/%s/segments/%d', $this->listId, $id));
    }

    public function pushSegmentMember(int $segmentId, string $mail): bool
    {
        return $this->sendRequest('POST', \sprintf('/lists/%s/segments/%d/members', $this->listId, $segmentId), [
            'email_address' => $mail,
        ]);
    }

    public function deleteSegmentMember(int $segmentId, string $mail): bool
    {
        return $this->sendRequest(
            'DELETE',
            \sprintf('/lists/%s/segments/%d/members/%s', $this->listId, $segmentId, $this->createHash($mail))
        );
    }

    public function createDynamicSegment(string $listId, EditSegmentRequest $request): ResponseInterface
    {
        return $this->send('POST', \sprintf('/lists/%s/segments', $listId), $request->toArray());
    }

    public function updateDynamicSegment(
        string $segmentId,
        string $listId,
        EditSegmentRequest $request,
    ): ResponseInterface {
        return $this->send('PATCH', \sprintf('/lists/%s/segments/%s', $listId, $segmentId), $request->toArray());
    }

    public function archiveMember(string $mail, string $listId): bool
    {
        return $this->sendRequest(
            'DELETE',
            \sprintf('/lists/%s/members/%s', $listId, $this->createHash($mail))
        );
    }

    public function deleteMember(string $mail, string $listId): bool
    {
        return $this->sendRequest('POST', \sprintf('/lists/%s/members/%s/actions/delete-permanent', $listId, $this->createHash($mail)));
    }

    public function getMemberInfo(string $mail, string $listId): array
    {
        $response = $this->send('GET', \sprintf('/lists/%s/members/%s?fields=status,contact_id,sms_subscription_status', $listId, $this->createHash($mail)));

        if ($this->isSuccessfulResponse($response)) {
            $data = $response->toArray();

            return [
                'status' => $data['status'] ?? null,
                'contact_id' => $data['contact_id'] ?? null,
                'sms_subscription_status' => $data['sms_subscription_status'] ?? null,
            ];
        }

        return ['status' => null, 'contact_id' => null, 'sms_subscription_status' => null];
    }

    public function getReportData(string $campaignId): array
    {
        $response = $this->send('GET', \sprintf('/reports/%s?fields=emails_sent,unsubscribed,opens,clicks', $campaignId));

        return $this->isSuccessfulResponse($response) ? $response->toArray() : [];
    }

    public function getReportOpenData(string $campaignId, int $offset): array
    {
        $response = $this->send('GET', \sprintf('/reports/%s/open-details?count=1000&offset=%d&fields=members.email_address,members.opens_count,members.proxy_excluded_opens_count,members.opens', $campaignId, $offset));

        return $this->isSuccessfulResponse($response) ? $response->toArray()['members'] : [];
    }

    public function getReportSentData(string $campaignId, int $offset): array
    {
        $response = $this->send('GET', \sprintf('/reports/%s/sent-to?count=1000&offset=%d&fields=sent_to.email_address', $campaignId, $offset));

        return $this->isSuccessfulResponse($response) ? $response->toArray()['sent_to'] : [];
    }

    public function getReportClickData(string $campaignId, int $offset): array
    {
        $response = $this->send('GET', \sprintf('/reports/%s/email-activity?count=1000&offset=%d&fields=emails.email_address,emails.activity', $campaignId, $offset));

        return $this->isSuccessfulResponse($response) ? $response->toArray()['emails'] : [];
    }

    public function getLastError(): ?string
    {
        if ($this->lastResponse && ($data = $this->lastResponse->toArray(false)) && isset($data['detail'])) {
            return $data['detail'];
        }

        return null;
    }

    public function isSuccessfulResponse(?ResponseInterface $response, bool $log = false): bool
    {
        try {
            $isSuccessful = $response && 200 <= $response->getStatusCode() && $response->getStatusCode() < 300;
        } catch (TransportExceptionInterface $e) {
            $isSuccessful = false;
        }

        if (!$isSuccessful && $log) {
            $this->logger->error(\sprintf('[API] Error: %s', $this->readErrorBody($response) ?: 'unknown'));
        }

        return $isSuccessful;
    }

    private function readErrorBody(?ResponseInterface $response): string
    {
        if (!$response) {
            return '';
        }

        try {
            return $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            return '[transport error] '.$e->getMessage();
        }
    }

    private function throwSyncException(string $responseContent): void
    {
        if (
            str_contains($responseContent, 'looks fake or invalid, please enter a real email address')
            || str_contains($responseContent, 'Please provide a valid email address')
        ) {
            throw new InvalidContactEmailException($responseContent);
        }

        if (str_contains($responseContent, 'contact must re-subscribe to get back on the list')) {
            throw new RemovedContactStatusException('Permanently deleted');
        }

        if (str_contains($responseContent, 'is already a list member in compliance state due to unsubscribe')) {
            throw new RemovedContactStatusException('Unsubscribed');
        }

        throw new FailedSyncException($responseContent);
    }

    private function throwContactSyncException(string $responseContent, ?string $phone): void
    {
        if (
            str_contains($responseContent, 'already subscribed to another contact')
            || str_contains($responseContent, 'already subscribed to our SMS marketing list')
            // Mailchimp leaks its internal SQL error when the phone hits a unique-key conflict on
            // their contact_sms_subscription table — same root cause as the messages above, route
            // to the same retry-without-SMS path.
            || (str_contains($responseContent, 'contact_sms_subscription.PRIMARY') && str_contains($responseContent, 'Duplicate entry'))
            // Phone flagged ineligible by Mailchimp (invalid number, opted-out network-wide, etc).
            // Same outcome: retry the sync without the SMS channel so the contact still gets its
            // email/profile update.
            || str_contains($responseContent, 'phone number cannot receive messages')
        ) {
            throw new SmsPhoneAlreadySubscribedException($phone ?? '', $responseContent);
        }

        if (str_contains($responseContent, 'Invalid Resource') || str_contains($responseContent, 'Invalid phone')) {
            throw new InvalidPayloadException($responseContent);
        }

        $this->throwSyncException($responseContent);
    }

    private function sendRequest(string $method, string $uri, array $body = [], bool $log = false): bool
    {
        $response = $this->send($method, $uri, $body);

        return $response ? $this->isSuccessfulResponse($response, $log) : false;
    }

    /**
     * @param iterable<ResponseInterface> $responses
     */
    public function stream(iterable $responses): ResponseStreamInterface
    {
        return $this->client->stream($responses);
    }

    public function send(string $method, string $uri, array $body = [], bool $blockOnResponseLog = true): ?ResponseInterface
    {
        $fullUri = '/3.0/'.ltrim($uri, '/');
        $isWriteCall = 'GET' !== $method;

        if ($this->debug) {
            $this->logger->info('[Mailchimp] Request : '.$fullUri, [
                'method' => $method,
                'body' => $body ?: null,
            ]);
        }

        try {
            $this->lastResponse = $this->client->request(
                $method,
                $fullUri,
                $body && \in_array($method, ['POST', 'PUT', 'PATCH'], true) ? ['json' => $body] : []
            );

            if ($this->debug && $isWriteCall && $blockOnResponseLog) {
                $this->logger->info('[Mailchimp] Response : '.$fullUri, [
                    'method' => $method,
                    'status' => $this->lastResponse->getStatusCode(),
                    'response_body' => $this->lastResponse->getContent(false),
                ]);
            }

            return $this->lastResponse;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(\sprintf('[API] Error: %s', $e->getMessage()), ['exception' => $e]);

            return null;
        }
    }

    private function createHash(string $email): string
    {
        return md5(strtolower($email));
    }
}
