<?php

namespace App\Formation\Moodle;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Driver
{
    public function __construct(private readonly HttpClientInterface $moodleClient)
    {
    }

    public function findUserByEmail(string $email): array
    {
        return $this->moodleClient->request('GET', '', [
            'query' => [
                'wsfunction' => 'core_user_get_users_by_field',
                'field' => 'username',
                'values' => [$email],
            ],
        ])->toArray()[0] ?? [];
    }

    public function createUser(array $data): array
    {
        return $this->moodleClient->request('POST', '', [
            'query' => ['wsfunction' => 'core_user_create_users'],
            'body' => [
                'users' => [$data],
            ],
        ])->toArray()[0] ?? [];
    }

    public function createJob(int $userId, array $data): ?int
    {
        $data = array_filter($data, static fn ($value) => null !== $value);

        $response = $this->moodleClient->request('POST', '', [
            'query' => ['wsfunction' => 'tool_organisation_create_job'],
            'body' => [
                'userid' => $userId,
                ...$data,
            ],
        ])->toArray();

        if (!empty($response['jobid'])) {
            return (int) $response['jobid'];
        }

        return null;
    }

    public function removeJob(int $jobId): void
    {
        $this->moodleClient->request('POST', '', [
            'query' => ['wsfunction' => 'tool_organisation_job_delete'],
            'body' => ['id' => $jobId],
        ]);
    }
}
