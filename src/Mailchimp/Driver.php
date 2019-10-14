<?php

namespace AppBundle\Mailchimp;

use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignRequest;
use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;
use AppBundle\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Driver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $client;
    private $listId;
    /** @var ResponseInterface|null */
    private $lastResponse;

    public function __construct(ClientInterface $client, string $listId)
    {
        $this->client = $client;
        $this->listId = $listId;
    }

    /**
     * Create or update a member
     */
    public function editMember(MemberRequest $request, string $listId): bool
    {
        return $this->sendRequest(
            'PUT',
            sprintf('/lists/%s/members/%s', $listId, md5($request->getMemberIdentifier())),
            $request->toArray()
        );
    }

    public function updateMemberTags(MemberTagsRequest $request, string $listId): bool
    {
        return $this->sendRequest(
            'POST',
            sprintf('/lists/%s/members/%s/tags', $listId, md5($request->getMemberIdentifier())),
            $request->toArray()
        );
    }

    public function getCampaignContent(string $campaignId): string
    {
        $response = $this->send('GET', sprintf('/campaigns/%s/content', $campaignId));

        if ($this->isSuccessfulResponse($response)) {
            return $this->toArray($response)['html'] ?? '';
        }

        $this->logger->error(sprintf('[API] Error: %s', $response->getBody()), ['campaignId' => $campaignId]);

        return '';
    }

    public function createCampaign(EditCampaignRequest $request): array
    {
        $response = $this->send('POST', '/campaigns', $request->toArray());

        return $this->isSuccessfulResponse($response) ? $this->toArray($response) : [];
    }

    public function updateCampaign(string $campaignId, EditCampaignRequest $request): array
    {
        $response = $this->send('PATCH', sprintf('/campaigns/%s', $campaignId), $request->toArray());

        return $this->isSuccessfulResponse($response) ? $this->toArray($response) : [];
    }

    public function editCampaignContent(string $campaignId, EditCampaignContentRequest $request): bool
    {
        return $this->sendRequest('PUT', sprintf('/campaigns/%s/content', $campaignId), $request->toArray());
    }

    public function deleteCampaign(string $campaignId): bool
    {
        return $this->sendRequest('DELETE', sprintf('/campaigns/%s', $campaignId));
    }

    public function sendCampaign(string $externalId): bool
    {
        return $this->sendRequest('POST', sprintf('/campaigns/%s/actions/send', $externalId));
    }

    public function sendTestCampaign(string $externalId, array $emails): bool
    {
        return $this->sendRequest('POST', sprintf('/campaigns/%s/actions/test', $externalId), [
            'test_emails' => $emails,
            'send_type' => 'html',
        ]);
    }

    public function createStaticSegment(string $name, array $emails = []): array
    {
        $response = $this->send('POST', sprintf('/lists/%s/segments', $this->listId), [
            'name' => $name,
            'static_segment' => $emails,
        ]);

        return $this->isSuccessfulResponse($response) ? $this->toArray($response) : [];
    }

    public function deleteStaticSegment(int $id): bool
    {
        return $this->sendRequest('DELETE', sprintf('/lists/%s/segments/%d', $this->listId, $id));
    }

    public function pushSegmentMember(int $segmentId, string $mail): bool
    {
        return $this->sendRequest('POST', sprintf('/lists/%s/segments/%d/members', $this->listId, $segmentId), [
            'email_address' => $mail,
        ]);
    }

    public function deleteSegmentMember(int $segmentId, string $mail): bool
    {
        return $this->sendRequest(
            'DELETE',
            sprintf('/lists/%s/segments/%d/members/%s', $this->listId, $segmentId, md5(strtolower($mail)))
        );
    }

    public function deleteMember(string $mail, string $listId): bool
    {
        return $this->sendRequest('POST', sprintf('/lists/%s/members/%s/actions/delete-permanent', $listId, md5(strtolower($mail))));
    }

    public function getReportData(string $campaignId): array
    {
        $response = $this->send('GET', sprintf('/reports/%s?fields=emails_sent,unsubscribed,opens,clicks,list_stats', $campaignId), []);

        return $this->isSuccessfulResponse($response) ? $this->toArray($response) : [];
    }

    public function getLastError(): ?string
    {
        if ($this->lastResponse && ($data = $this->toArray($this->lastResponse)) && isset($data['detail'])) {
            return $data['detail'];
        }

        return null;
    }

    private function sendRequest(string $method, string $uri, array $body = []): bool
    {
        $response = $this->send($method, $uri, $body);

        return $response ? $this->isSuccessfulResponse($response) : false;
    }

    private function send(string $method, string $uri, array $body = []): ?ResponseInterface
    {
        try {
            return $this->lastResponse = $this->client->request(
                $method,
                '/3.0/'.ltrim($uri, '/'),
                ($body && \in_array($method, ['POST', 'PUT', 'PATCH'], true) ? ['json' => $body] : [])
            );
        } catch (RequestException $e) {
            $this->logger->error(sprintf(
                '[API] Error: %s',
                ($response = $e->getResponse()) ? $response->getBody() : 'Unknown'
            ), ['exception' => $e]);

            return $this->lastResponse = $response;
        }
    }

    private function isSuccessfulResponse(?ResponseInterface $response): bool
    {
        return $response && 200 <= $response->getStatusCode() && $response->getStatusCode() < 300;
    }

    private function toArray(ResponseInterface $response): array
    {
        return \GuzzleHttp\json_decode((string) $response->getBody(), true);
    }
}
