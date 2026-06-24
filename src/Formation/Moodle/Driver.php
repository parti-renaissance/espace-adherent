<?php

declare(strict_types=1);

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

    public function findUserById(int $id): array
    {
        return $this->moodleClient->request('GET', '', [
            'query' => [
                'wsfunction' => 'core_user_get_users_by_field',
                'field' => 'id',
                'values' => [$id],
            ],
        ])->toArray()[0] ?? [];
    }

    public function updateUser(int $userId, array $data): void
    {
        $this->execute('core_user_update_users', [
            'users' => [['id' => $userId, ...$data]],
        ]);
    }

    public function deleteUser(int $userId): void
    {
        $this->execute('core_user_delete_users', ['userids' => [$userId]]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findUsersByName(string $firstName, string $lastName): array
    {
        return $this->moodleClient->request('POST', '', [
            'query' => ['wsfunction' => 'core_user_get_users'],
            'body' => [
                'criteria' => [
                    ['key' => 'firstname', 'value' => $firstName],
                    ['key' => 'lastname', 'value' => $lastName],
                ],
            ],
        ])->toArray()['users'] ?? [];
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

    private function execute(string $wsFunction, array $body): void
    {
        $content = $this->moodleClient->request('POST', '', [
            'query' => ['wsfunction' => $wsFunction],
            'body' => $body,
        ])->getContent();

        $decoded = '' === $content ? null : json_decode($content, true);

        if (\is_array($decoded) && isset($decoded['exception'])) {
            throw new \RuntimeException(\sprintf('Moodle "%s" call failed: %s [%s] %s', $wsFunction, $decoded['exception'], $decoded['errorcode'] ?? 'unknown', $decoded['message'] ?? ''));
        }
    }

    public function createDepartment(string $name, string $id, ?string $parentId): bool
    {
        $response = $this->moodleClient->request('POST', '', [
            'query' => ['wsfunction' => 'tool_organisation_create_departments'],
            'body' => ['departments' => [[
                'name' => $name,
                'idnumber' => $id,
                'parent' => $parentId,
            ]]],
        ])->toArray();

        return !empty($response['result'][0]['id']);
    }
}
