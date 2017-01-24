<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

class CommitteeFactory
{
    public function createFromArray(array $data): Committee
    {
        foreach (['name', 'description', 'created_by', 'country'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = isset($data['uuid'])
            ? Uuid::fromString($data['uuid'])
            : Committee::createUuid($data['name']);

        $committee = Committee::createSimple(
            $uuid,
            $data['created_by'],
            $data['name'],
            $data['description'],
            $data['country']
        );

        if (!empty($data['postal_code']) && !empty($data['city_code'])) {
            $committee->setLocation($data['postal_code'], $data['city_code'], $data['address'] ?? null);
        }

        $committee->setSocialNetworks(
            $data['facebook_page_url'] ?? null,
            $data['twitter_nickname'] ?? null,
            $data['google_plus_page_url'] ?? null
        );

        return $committee;
    }

    /**
     * Returns a new instance of Committee from a CreateCommitteeCommand DTO.
     *
     * @param CommitteeCreationCommand $command
     *
     * @return Committee
     */
    public function createFromCommitteeCreationCommand(CommitteeCreationCommand $command): Committee
    {
        $committee = Committee::createForAdherent(
            $command->getAdherent(),
            $command->name,
            $command->description,
            $command->country
        );

        if ($command->postalCode) {
            $committee->setLocation($command->postalCode, $command->city, $command->address);
        }

        if ($command->facebookPageUrl) {
            $committee->setFacebookPageUrl($command->facebookPageUrl);
        }

        if ($command->twitterNickname) {
            $committee->setTwitterNickname($command->twitterNickname);
        }

        if ($command->googlePlusPageUrl) {
            $committee->setGooglePlusPageUrl($command->googlePlusPageUrl);
        }

        return $committee;
    }
}
