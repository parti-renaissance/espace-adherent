<?php

namespace App\CitizenProject;

use App\Address\NullableAddress;
use App\Entity\CitizenProject;
use App\Entity\CitizenProjectCommitteeSupport;

class CitizenProjectUpdateCommand extends CitizenProjectCommand
{
    public static function createFromCitizenProject(CitizenProject $citizenProject): self
    {
        $address = $citizenProject->getPostAddress() ? NullableAddress::createFromAddress($citizenProject->getPostAddress()) : null;
        $dto = new self($address);
        $dto->name = $citizenProject->getName();
        $dto->subtitle = $citizenProject->getSubtitle();
        $dto->category = $citizenProject->getCategory();
        $dto->phone = $citizenProject->getPhone();
        $dto->committeeSupports = $citizenProject->getCommitteeSupports();
        $dto->problemDescription = $citizenProject->getProblemDescription();
        $dto->proposedSolution = $citizenProject->getProposedSolution();
        $dto->requiredMeans = $citizenProject->getRequiredMeans();
        $dto->citizenProject = $citizenProject;
        $dto->skills = $citizenProject->getSkills();
        $dto->district = $citizenProject->getDistrict();

        if ($citizenProject->getPostAddress()) {
            $dto->address->setAddress(null); // Fix the Citizen Project nullable address
        }

        /** @var CitizenProjectCommitteeSupport $committeeSupport */
        foreach ($dto->committeeSupports as $committeeSupport) {
            $dto->addCommittee($committeeSupport->getCommittee());
        }

        return $dto;
    }
}
