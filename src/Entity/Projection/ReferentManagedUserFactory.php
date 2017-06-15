<?php

namespace AppBundle\Entity\Projection;

class ReferentManagedUserFactory
{
    public function createFromArray(array $data): ReferentManagedUser
    {
        return new ReferentManagedUser(
            $data['status'],
            $data['type'],
            $data['original_id'],
            $data['email'],
            $data['postal_code'],
            isset($data['city']) ? $data['city'] : null,
            isset($data['country']) ? $data['country'] : null,
            isset($data['first_name']) ? $data['first_name'] : null,
            isset($data['last_name']) ? $data['last_name'] : null,
            isset($data['birthday']) ? is_int($data['birthday']) ?: $this->getAge($data['birthday']) : null,
            isset($data['phone']) ? $data['phone'] : null,
            isset($data['committees']) ? $data['committees'] : null,
            $data['is_committee_member'],
            $data['is_committee_host'],
            $data['is_mail_subscriber'],
            $data['created_at'] instanceOf \DateTime ?: new \DateTime($data['created_at'])
        );
    }

    private function getAge($date): int
    {
        $age = date('Y') - date('Y', $date->getTimestamp());

        if (date('md') < date('md', $date->getTimestamp())) {
            return $age - 1;
        }

        return $age;
    }
}
