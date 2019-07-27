<?php

namespace AppBundle\Entity\ApplicationRequest;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\ApplicationRequest\ApplicationRequestTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="application_request_running_mate")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ApplicationRequest\RunningMateRequestRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class RunningMateRequest extends ApplicationRequest
{
    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $curriculumName;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "application/pdf",
     *         "application/x-pdf"
     *     },
     *     mimeTypesMessage="application_request.curriculum.mime_type"
     * )
     */
    private $curriculum;

    private $removeCurriculum = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isLocalAssociationMember = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $localAssociationDomain;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isPoliticalActivist = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $politicalActivistDetails;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isPreviousElectedOfficial = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $previousElectedOfficialDetails;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="text")
     */
    private $favoriteThemeDetails;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="text")
     */
    private $projectDetails;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="text")
     */
    private $professionalAssets;

    public function hasCurriculumUploaded(): bool
    {
        return null !== $this->curriculumName;
    }

    public function getCurriculum(): ?UploadedFile
    {
        return $this->curriculum;
    }

    public function removeCurriculumName(): void
    {
        $this->curriculumName = null;
    }

    public function setCurriculum(?UploadedFile $curriculum): void
    {
        $this->curriculum = $curriculum;
    }

    public function setCurriculumName(string $curriculumName): void
    {
        $this->curriculumName = $curriculumName;
    }

    public function getRemoveCurriculum(): bool
    {
        return $this->removeCurriculum;
    }

    public function setRemoveCurriculum(bool $removeCurriculum): void
    {
        $this->removeCurriculum = $removeCurriculum;
    }

    public function isLocalAssociationMember(): ?bool
    {
        return $this->isLocalAssociationMember;
    }

    public function setIsLocalAssociationMember(bool $isLocalAssociationMember): void
    {
        $this->isLocalAssociationMember = $isLocalAssociationMember;
    }

    public function getLocalAssociationDomain(): ?string
    {
        return $this->localAssociationDomain;
    }

    public function setLocalAssociationDomain(?string $localAssociationDomain): void
    {
        $this->localAssociationDomain = $localAssociationDomain;
    }

    public function isPoliticalActivist(): ?bool
    {
        return $this->isPoliticalActivist;
    }

    public function setIsPoliticalActivist(bool $isPoliticalActivist): void
    {
        $this->isPoliticalActivist = $isPoliticalActivist;
    }

    public function getPoliticalActivistDetails(): ?string
    {
        return $this->politicalActivistDetails;
    }

    public function setPoliticalActivistDetails(?string $politicalActivistDetails): void
    {
        $this->politicalActivistDetails = $politicalActivistDetails;
    }

    public function isPreviousElectedOfficial(): ?bool
    {
        return $this->isPreviousElectedOfficial;
    }

    public function setIsPreviousElectedOfficial(bool $isPreviousElectedOfficial): void
    {
        $this->isPreviousElectedOfficial = $isPreviousElectedOfficial;
    }

    public function getPreviousElectedOfficialDetails(): ?string
    {
        return $this->previousElectedOfficialDetails;
    }

    public function setPreviousElectedOfficialDetails(?string $previousElectedOfficialDetails): void
    {
        $this->previousElectedOfficialDetails = $previousElectedOfficialDetails;
    }

    public function getFavoriteThemeDetails(): ?string
    {
        return $this->favoriteThemeDetails;
    }

    public function setFavoriteThemeDetails(?string $favoriteThemeDetails): void
    {
        $this->favoriteThemeDetails = $favoriteThemeDetails;
    }

    public function getProjectDetails(): ?string
    {
        return $this->projectDetails;
    }

    public function setProjectDetails(?string $projectDetails): void
    {
        $this->projectDetails = $projectDetails;
    }

    public function getProfessionalAssets(): ?string
    {
        return $this->professionalAssets;
    }

    public function setProfessionalAssets(?string $professionalAssets): void
    {
        $this->professionalAssets = $professionalAssets;
    }

    public function getPathWithDirectory(): string
    {
        return sprintf('%s/%s', 'files/application_requests/curriculum', $this->curriculumName);
    }

    public function setCurriculumNameFromUploadedFile(UploadedFile $curriculum): void
    {
        $this->curriculumName = sprintf('%s.%s',
            md5(sprintf('%s@%s', $this->getUuid(), $curriculum->getClientOriginalName())),
            $curriculum->getClientOriginalExtension()
        );
    }

    public function isLocalAssociationMemberAsString(): string
    {
        return $this->isLocalAssociationMember ? 'Oui' : 'Non';
    }

    public function isPoliticalActivistAsString(): string
    {
        return $this->isPoliticalActivist ? 'Oui' : 'Non';
    }

    public function isPreviousElectedOfficialAsString(): string
    {
        return $this->isPreviousElectedOfficial ? 'Oui' : 'Non';
    }

    public function getType(): string
    {
        return ApplicationRequestTypeEnum::RUNNING_MATE;
    }
}
