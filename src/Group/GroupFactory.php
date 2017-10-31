<?php

namespace AppBundle\Group;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\Group;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;

class GroupFactory
{
    private $addressFactory;

    public function __construct(PostAddressFactory $addressFactory = null)
    {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromArray(array $data): Group
    {
        foreach (['name', 'description', 'created_by'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $phone = null;
        if (isset($data['phone'])) {
            $phone = $this->createPhone($data['phone']);
        }

        $uuid = isset($data['uuid'])
            ? Uuid::fromString($data['uuid'])
            : Group::createUuid($data['name']);

        $group = Group::createSimple(
            $uuid,
            $data['created_by'],
            $data['name'],
            $data['description'],
            $data['address'] ?? null,
            $phone,
            $data['created_at'] ?? 'now'
        );

        if (isset($data['slug'])) {
            $group->updateSlug($data['slug']);
        }

        return $group;
    }

    /**
     * Returns a new instance of Group from a CreateGroupCommand DTO.
     *
     * @param GroupCreationCommand $command
     *
     * @return Group
     */
    public function createFromGroupCreationCommand(GroupCreationCommand $command): Group
    {
        $group = Group::createForAdherent(
            $command->getAdherent(),
            $command->name,
            $command->description,
            $command->getAddress() ? $this->addressFactory->createFromNullableAddress($command->getAddress()) : null,
            $command->getPhone()
        );

        return $group;
    }

    /**
     * Returns a PhoneNumber object.
     *
     * The format must be something like "33 0102030405"
     *
     * @param string $phoneNumber
     *
     * @return PhoneNumber
     */
    private function createPhone($phoneNumber): PhoneNumber
    {
        list($country, $number) = explode(' ', $phoneNumber);

        $phone = new PhoneNumber();
        $phone->setCountryCode($country);
        $phone->setNationalNumber($number);

        return $phone;
    }
}
