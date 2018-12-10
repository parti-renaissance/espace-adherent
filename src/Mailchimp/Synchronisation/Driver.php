<?php

namespace AppBundle\Mailchimp\Synchronisation;

use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;
use AppBundle\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Driver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $client;
    private $listId;

    public function __construct(ClientInterface $client, string $listId)
    {
        $this->client = $client;
        $this->listId = $listId;
    }

    /**
     * Create or update a member
     */
    public function editMember(MemberRequest $request): bool
    {
        return $this->sendRequest(
            'PUT',
            sprintf('/lists/%s/members/%s', $this->listId, md5($request->getMemberIdentifier())),
            $request->toArray()
        );
    }

    /**
     * Update member's tags
     */
    public function updateMemberTags(MemberTagsRequest $request): bool
    {
        return $this->sendRequest(
            'POST',
            sprintf('/lists/%s/members/%s/tags', $this->listId, md5($request->getMemberIdentifier())),
            $request->toArray()
        );
    }

    private function sendRequest(string $method, string $uri, array $body = []): bool
    {
        try {
            $response = $this->client->request(
                $method,
                '/3.0'.$uri,
                ($body && \in_array($method, ['POST', 'PUT', 'PATCH'], true) ? ['json' => $body] : [])
            );

            return 200 <= $response->getStatusCode() && $response->getStatusCode() < 300;
        } catch (RequestException $e) {
            $this->logger->warning(sprintf(
                '[API] Error: %s',
                ($response = $e->getResponse()) ? $response->getBody() : 'Unknown'
            ), ['exception' => $e]);
        }

        return false;
    }
}
