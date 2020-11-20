<?php

namespace App\CitizenProject;

use App\Address\PostAddressFactory;
use App\Entity\CitizenProject;
use App\Referent\ReferentTagManager;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;

class CitizenProjectFactory
{
    private $addressFactory;
    private $referentTagManager;

    public function __construct(ReferentTagManager $referentTagManager, PostAddressFactory $addressFactory = null)
    {
        $this->referentTagManager = $referentTagManager;
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromArray(array $data): CitizenProject
    {
        foreach (['uuid', 'name', 'subtitle', 'category', 'address', 'problem_description', 'proposed_solution', 'required_means', 'created_by'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException(sprintf('Key "%s" is missing or has an empty value.', $key));
            }
        }

        $uuid = isset($data['uuid'])
            ? Uuid::fromString($data['uuid'])
            : Uuid::uuid4();

        $phone = null;
        if (isset($data['phone'])) {
            $phone = PhoneNumberUtils::create($data['phone']);
        }

        $citizenProject = new CitizenProject(
            $uuid,
            Uuid::fromString($data['created_by']),
            $data['name'],
            $data['subtitle'],
            $data['category'],
            $data['committee'] ?? [],
            $data['problem_description'],
            $data['proposed_solution'],
            $data['required_means'],
            $phone,
            $data['address'],
            $data['district'] ?? null,
            $data['turnkey_project'] ?? null,
            $data['slug'] ?? null,
            $data['status'] ?? CitizenProject::PENDING,
            $data['approved_at'] ?? null,
            $data['created_at'] ?? 'now'
        );

        if (isset($data['skills'])) {
            $citizenProject->setSkills($data['skills']);
        }

        $this->referentTagManager->assignReferentLocalTags($citizenProject);

        return $citizenProject;
    }

    /**
     * Returns a new instance of CitizenProject from a CreateCitizenProjectCommand DTO.
     */
    public function createFromCitizenProjectCreationCommand(CitizenProjectCreationCommand $command): CitizenProject
    {
        $citizenProject = CitizenProject::createForAdherent(
            $command->getAdherent(),
            $command->name,
            $command->subtitle,
            $command->category,
            $command->getPhone(),
            $command->problemDescription,
            $command->proposedSolution,
            $command->requiredMeans,
            $command->getCommittees()->toArray(),
            $command->getAddress() ? $this->addressFactory->createFromNullableAddress($command->getAddress()) : null,
            $command->getDistrict(),
            $command->getTurnkeyProject()
        );

        $citizenProject->setSkills($command->getSkills());
        $citizenProject->setImage($command->getImage());

        return $citizenProject;
    }
}
