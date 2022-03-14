<?php

namespace App\Entity\Projection;

class ManagedUserFactory
{
    public function createFromArray(array $data): ManagedUser
    {
        return new ManagedUser(
            $data['status'],
            $data['source'],
            $data['original_id'],
            $data['email'],
            $data['address'] ?? null,
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
            $data['is_committee_member'] ?? 0,
            $data['is_committee_host'] ?? 0,
            $data['is_committee_provisional_supervisor'] ?? 0,
            $data['is_committee_supervisor'] ?? 0,
            $data['subscription_types'] ?? [],
            $data['zones'] ?? [],
            $data['subscribedTags'] ?? null,
            $data['created_at'] instanceof \DateTime ?: new \DateTime($data['created_at']),
            $data['gender'] ?? null,
            $data['supervisor_tags'] ?? [],
            $data['uuid'] ?? null,
            $data['vote_committee_id'] ?? null,
            isset($data['certified_at']) ? new \DateTime($data['certified_at']) : null,
            $data['interests'] ?? []
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
