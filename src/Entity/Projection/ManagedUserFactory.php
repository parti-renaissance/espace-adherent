<?php

namespace App\Entity\Projection;

use App\Mailchimp\Contact\ContactStatusEnum;

class ManagedUserFactory
{
    public function createFromArray(array $data): ManagedUser
    {
        $managedUser = new ManagedUser(
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
            $data['birthday'] ?? null,
            isset($data['birthday']) ? \is_int($data['birthday']) ?: $this->getAge($data['birthday']) : null,
            $data['phone'] ?? null,
            $data['nationality'] ?? null,
            $data['committees'] ?? null,
            $data['committee_uuids'] ?? null,
            $data['tags'] ?? null,
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
            isset($data['last_membership_donation']) ? new \DateTime($data['last_membership_donation']) : null,
            isset($data['first_membership_donation']) ? new \DateTime($data['first_membership_donation']) : null,
            $data['committee'] ?? null,
            $data['committee_uuid'] ?? null,
            $data['agora'] ?? null,
            $data['agora_uuid'] ?? null,
            $data['interests'] ?? [],
            $data['mandates'] ?? [],
            $data['declared_mandates'] ?? [],
            $data['cotisation_dates'] ?? []
        );

        $managedUser->mailchimpStatus = ContactStatusEnum::SUBSCRIBED;

        return $managedUser;
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
