<?php

namespace App\Committee;

use App\Address\PostAddressFactory;
use App\Committee\DTO\CommitteeCreationCommand;
use App\Entity\Committee;
use App\Geo\ZoneMatcher;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;

class CommitteeFactory
{
    private $addressFactory;

    /**
     * @var ZoneMatcher
     */
    private $zoneMatcher;

    public function __construct(
        ZoneMatcher $zoneMatcher,
        ?PostAddressFactory $addressFactory = null,
    ) {
        $this->zoneMatcher = $zoneMatcher;
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromArray(array $data): Committee
    {
        foreach (['name', 'description', 'created_by', 'address'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(\sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $phone = null;
        if (isset($data['phone'])) {
            $phone = PhoneNumberUtils::create($data['phone']);
        }

        $uuid = isset($data['uuid']) ? Uuid::fromString($data['uuid']) : Committee::createUuid($data['name']);

        $committee = Committee::createSimple(
            $uuid,
            $data['created_by'],
            $data['name'],
            $data['description'],
            $data['address'],
            $phone,
            $data['created_at'] ?? 'now'
        );

        $committee->setNameLocked($data['name_locked'] ?? false);
        $committee->setSocialNetworks(
            $data['facebook_page_url'] ?? null,
            $data['twitter_nickname'] ?? null
        );

        if (isset($data['slug'])) {
            $committee->updateSlug($data['slug']);
        }

        if (isset($data['status'])) {
            $committee->setStatus($data['status']);
        }

        $zones = $this->zoneMatcher->match($committee->getPostAddress());
        foreach ($zones as $zone) {
            $committee->addZone($zone);
        }

        return $committee;
    }

    /**
     * Returns a new instance of Committee from a CreateCommitteeCommand DTO.
     */
    public function createFromCommitteeCreationCommand(CommitteeCreationCommand $command): Committee
    {
        $committee = Committee::createForAdherent(
            $command->getAdherent(),
            $command->name,
            $command->description,
            $this->addressFactory->createFromAddress($command->getAddress(), true),
            $command->getPhone()
        );

        if ($command->facebookPageUrl) {
            $committee->setFacebookPageUrl($command->facebookPageUrl);
        }

        if ($command->twitterNickname) {
            $committee->setTwitterNickname($command->twitterNickname);
        }

        return $committee;
    }
}
