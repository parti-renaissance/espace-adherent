<?php

declare(strict_types=1);

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
            isset($data['birthday']) ? ($data['birthday'] instanceof \DateTimeImmutable ? $data['birthday'] : \DateTimeImmutable::createFromInterface($data['birthday'])) : null,
            isset($data['birthday']) ? \is_int($data['birthday']) ?: $this->getAge($data['birthday']) : null,
            $data['phone'] ?? null,
            $data['nationality'] ?? null,
            $data['committees'] ?? null,
            $data['committee_uuids'] ?? null,
            $data['tags'] ?? null,
            $data['is_committee_member'] ?? 0,
            $data['is_committee_host'] ?? 0,
            $data['is_committee_supervisor'] ?? 0,
            $data['subscription_types'] ?? [],
            $data['zones'] ?? [],
            $data['subscribedTags'] ?? null,
            $data['created_at'] instanceof \DateTimeImmutable ?: new \DateTimeImmutable($data['created_at']),
            $data['gender'] ?? null,
            $data['supervisor_tags'] ?? [],
            $data['uuid'] ?? null,
            $data['vote_committee_id'] ?? null,
            isset($data['certified_at']) ? new \DateTimeImmutable($data['certified_at']) : null,
            isset($data['last_membership_donation']) ? new \DateTimeImmutable($data['last_membership_donation']) : null,
            isset($data['first_membership_donation']) ? new \DateTimeImmutable($data['first_membership_donation']) : null,
            $data['committee'] ?? null,
            $data['committee_uuid'] ?? null,
            $data['agora'] ?? null,
            $data['agora_uuid'] ?? null,
            $data['interests'] ?? [],
            $data['mandates'] ?? [],
            $data['declared_mandates'] ?? [],
            $data['cotisation_dates'] ?? [],
            null,
            $data['image_name'] ?? null
        );

        $managedUser->mailchimpStatus = ContactStatusEnum::SUBSCRIBED;

        // Pre-computed JSON columns (populated by Go worker)
        if (isset($data['sessions'])) {
            $managedUser->sessions = $data['sessions'];
        }
        if (isset($data['adherent_tags'])) {
            $managedUser->adherentTags = $data['adherent_tags'];
        }
        if (isset($data['static_tags'])) {
            $managedUser->staticTags = $data['static_tags'];
        }
        if (isset($data['elect_tags'])) {
            $managedUser->electTags = $data['elect_tags'];
        }
        if (isset($data['instances'])) {
            $managedUser->instances = $data['instances'];
        }
        if (isset($data['subscriptions'])) {
            $managedUser->subscriptions = $data['subscriptions'];
        }
        if (isset($data['civility'])) {
            $managedUser->civility = $data['civility'];
        }

        return $managedUser;
    }

    private function getAge(\DateTimeInterface $date): int
    {
        $age = (int) date('Y') - (int) date('Y', $date->getTimestamp());

        if ((int) date('md') < (int) date('md', $date->getTimestamp())) {
            return $age - 1;
        }

        return $age;
    }
}
