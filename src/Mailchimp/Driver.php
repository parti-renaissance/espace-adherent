<?php

namespace App\Mailchimp;

use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Mailchimp\Campaign\Request\EditCampaignRequest;
use App\Mailchimp\Exception\FailedSyncException;
use App\Mailchimp\Exception\InvalidContactEmailException;
use App\Mailchimp\Exception\RemovedContactStatusException;
use App\Mailchimp\MailchimpSegment\Request\EditSegmentRequest;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Driver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private HttpClientInterface $client;
    private string $listId;
    private ?ResponseInterface $lastResponse = null;

    public function __construct(HttpClientInterface $mailchimpClient, string $listId)
    {
        $this->client = $mailchimpClient;
        $this->listId = $listId;
    }

    /**
     * Create or update a member
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
            $responseContent = $response->getContent(false);

            if (
                str_contains($responseContent, 'looks fake or invalid, please enter a real email address')
                || str_contains($responseContent, 'Please provide a valid email address')
            ) {
                throw new InvalidContactEmailException();
            } elseif (str_contains($responseContent, 'contact must re-subscribe to get back on the list')) {
                throw new RemovedContactStatusException('Permanently deleted');
            } elseif (str_contains($responseContent, 'is already a list member in compliance state due to unsubscribe')) {
                throw new RemovedContactStatusException('Unsubscribed');
            }

            throw new FailedSyncException($responseContent);
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

    public function createStaticSegment(string $name, string $listId, array $emails = []): ResponseInterface
    {
        return $this->send('POST', \sprintf('/lists/%s/segments', $listId), [
            'name' => $name,
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

    public function getMemberStatus(string $mail, string $listId): ?string
    {
        $response = $this->send('GET', \sprintf('/lists/%s/members/%s?fields=status', $listId, $this->createHash($mail)));

        if ($this->isSuccessfulResponse($response)) {
            return $response->toArray()['status'] ?? null;
        }

        return null;
    }

    public function getReportData(string $campaignId): array
    {
        $response = $this->send('GET', \sprintf('/reports/%s?fields=emails_sent,unsubscribed,opens,clicks,list_stats', $campaignId));

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
            $this->logger->error(\sprintf('[API] Error: %s', $response ? $response->getContent(false) : 'unknown'));
        }

        return $isSuccessful;
    }

    private function sendRequest(string $method, string $uri, array $body = [], bool $log = false): bool
    {
        $response = $this->send($method, $uri, $body);

        return $response ? $this->isSuccessfulResponse($response, $log) : false;
    }

    private function send(string $method, string $uri, array $body = []): ?ResponseInterface
    {
        try {
            return $this->lastResponse = $this->client->request(
                $method,
                '/3.0/'.ltrim($uri, '/'),
                $body && \in_array($method, ['POST', 'PUT', 'PATCH'], true) ? ['json' => $body] : []
            );
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
