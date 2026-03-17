<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\Mailchimp\Driver;

class MailchimpStaticSegmentService implements MailchimpStaticSegmentServiceInterface
{
    public function __construct(
        private readonly Driver $driver,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
    ) {
    }

    public function create(string $name, array $emails = [], ?string $listId = null): ?int
    {
        $listId ??= $this->mailchimpObjectIdMapping->getMainListId();

        $response = $this->driver->createStaticSegment($name, $listId, $emails);
        $responseData = $response->toArray(false);

        if (200 === $response->getStatusCode()) {
            return $responseData['id'] ?? null;
        }

        if (400 === $response->getStatusCode() && isset($responseData['detail'])) {
            if ('Sorry, that tag already exists.' === $responseData['detail']) {
                return $this->findSegmentId($name, $listId);
            }

            // Retry without invalid emails
            if ($invalidEmails = $this->extractInvalidEmails($responseData)) {
                $validEmails = array_diff($emails, $invalidEmails);
                if (\count($validEmails) > 0) {
                    return $this->create($name, array_values($validEmails), $listId);
                }
            }
        }

        return null;
    }

    public function update(int $segmentId, array $emails, ?string $listId = null): bool
    {
        $listId ??= $this->mailchimpObjectIdMapping->getMainListId();

        $response = $this->driver->updateStaticSegment($segmentId, $listId, $emails);

        if ($this->driver->isSuccessfulResponse($response)) {
            return true;
        }

        $responseData = $response->toArray(false);

        // Retry without invalid emails
        if (400 === $response->getStatusCode() && ($invalidEmails = $this->extractInvalidEmails($responseData))) {
            $validEmails = array_diff($emails, $invalidEmails);
            if (\count($validEmails) > 0) {
                return $this->update($segmentId, array_values($validEmails), $listId);
            }
        }

        return false;
    }

    private function extractInvalidEmails(array $responseData): array
    {
        $invalidEmails = [];
        $errors = $responseData['errors'] ?? [];

        if (!\is_array($errors)) {
            return [];
        }

        foreach ($errors as $error) {
            if (!\is_array($error)) {
                continue;
            }

            if ('static_segment' !== ($error['field'] ?? null)) {
                continue;
            }

            $message = $error['message'] ?? '';
            if (!\is_string($message)) {
                continue;
            }

            // Format: "Invalid email addresses given in static_segment: email1, email2"
            if (preg_match('/Invalid email addresses given in static_segment:\s*(.+)$/i', $message, $matches)) {
                $emails = array_map('trim', explode(',', $matches[1]));
                $invalidEmails = array_merge($invalidEmails, $emails);
            }
        }

        return $invalidEmails;
    }

    public function createOrUpdate(string $name, array $emails, ?int $segmentId = null, ?string $listId = null): int|bool|null
    {
        if (null === $segmentId) {
            return $this->create($name, $emails, $listId);
        }

        return $this->update($segmentId, $emails, $listId);
    }

    private function findSegmentId(string $segmentName, string $listId): ?int
    {
        $offset = 0;
        $limit = 1000;

        while ($segments = $this->driver->getSegments($listId, $offset, $limit)) {
            foreach ($segments as $segment) {
                if ($segment['name'] === $segmentName) {
                    return $segment['id'];
                }
            }

            if (\count($segments) < $limit) {
                break;
            }

            $offset += $limit;
        }

        return null;
    }
}
