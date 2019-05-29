<?php

namespace AppBundle\Entity\ApplicationRequest;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="application_request_running_mate")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class RunningMateRequest extends ApplicationRequest
{
    /**
     * @var string|null
     *
     * @ORM\Column
     */
    private $curriculumName;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     mimeTypes={
     *         "application/pdf",
     *         "application/x-pdf",
     *         "application/vnd.ms-powerpoint",
     *         "application/vnd.openxmlformats-officedocument.presentationml.presentation",
     *         "application/msword",
     *         "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
     *     },
     * )
     */
    private $curriculum;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isLocalAssociationMember;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $localAssociationDomain;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isPoliticalActivist;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $politicalActivistDetails;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isPreviousElectedOfficial;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $previousElectedOfficialDetails;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $favoriteThemeDetails;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $projectDetails;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $professionalAssets;

    public function getCurriculum(): ?UploadedFile
    {
        return $this->curriculum;
    }

    public function setCurriculum(?UploadedFile $curriculum): void
    {
        $this->curriculum = $curriculum;
    }

    public function setCurriculumNameFromUploadedFile(?UploadedFile $curriculum): void
    {
        $this->curriculumName = null === $curriculum ? null :
            sprintf('%s.%s',
                md5(sprintf('%s@%s', $this->getUuid(), $curriculum->getClientOriginalName())),
                $curriculum->getClientOriginalExtension()
            )
        ;
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
        return sprintf('%s/%s', 'curriculum', $this->curriculumName);
    }
}
