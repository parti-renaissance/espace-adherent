<?php

namespace App\Mailchimp\Synchronisation;

use App\Entity\MailchimpSegment;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class MailchimpSegmentHandler
{
    private $client;
    private $entityManager;
    private $mailchimpElectedRepresentativeListId;
    private $logger;

    public function __construct(
        ClientInterface $mailchimpClient,
        EntityManagerInterface $entityManager,
        string $mailchimpElectedRepresentativeListId,
        LoggerInterface $logger
    ) {
        $this->client = $mailchimpClient;
        $this->entityManager = $entityManager;
        $this->mailchimpElectedRepresentativeListId;
        $this->logger = $logger;
    }

    public function post(MailchimpSegment $mailchimpSegment): void
    {
        $url = sprintf('/3.0/lists/%s/segments', $this->mailchimpElectedRepresentativeListId);

        try {
            $response = $this->client->request('POST', $url, ['json' => [
                'name' => $mailchimpSegment->getLabel(),
                'static_segment' => [],
            ]]);
        } catch (RequestException $e) {
            $this->logger->warning($e->getRequest() ? (string) $e->getResponse()->getBody() : $e->getMessage());

            return;
        }

        if (200 === $response->getStatusCode()) {
            $data = json_decode((string) $response->getBody(), true);

            if (isset($data['id'])) {
                $mailchimpSegment->setExternalId($data['id']);
                $this->entityManager->flush();
            }
        }
    }
}
