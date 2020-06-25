<?php

namespace App\Entity\Projection;

class ManagedUserFactory
{
    public function createFromArray(array $data): ManagedUser
    {
        return new ManagedUser(
            $data['status'],
            $data['type'],
            $data['original_id'],
            $data['email'],
            $data['postal_code'],
            $data['committee_postal_code'] ?? null,
            $data['city'] ?? null,
            $data['country'] ?? null,
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            isset($data['birthday']) ? \is_int($data['birthday']) ?: $this->getAge($data['birthday']) : null,
            $data['phone'] ?? null,
            $data['committees'] ?? null,
            $data['committee_uuids'] ?? null,
            $data['is_committee_member'],
            $data['is_committee_host'],
            $data['is_committee_supervisor'],
            $data['subscription_types'],
            $data['subscribedTags'],
            $data['created_at'] instanceof \DateTime ?: new \DateTime($data['created_at']),
            $data['gender'] ?? null,
            $data['supervisor_tags'] ?? [],
            $data['citizenProjects'] ?? null,
            $data['citizenProjectsOrganizer'] ?? null,
            $data['uuid'] ?? null,
            $data['vote_committee_id'] ?? null
        );
    }

    private function getAge(\DateTimeInterface $date): int
    {
        $age = date('Y') - date('Y', $date->getTimestamp());

        if (date('md') < date('md', $date->getTimestamp())) {
            return $age - 1;
        }

        return $age;
    }
}
