<?php

namespace App\Entity\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\OfficialReportRepository"))
 * @ORM\Table(name="territorial_council_official_report")
 */
class OfficialReport
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50)
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private $name;

    /**
     * @var UploadedFile|null
     */
    #[Assert\File(maxSize: '5M', mimeTypes: OfficialReportDocument::MIME_TYPES, mimeTypesMessage: 'territorail_council.official_report.mime_type')]
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\PoliticalCommittee")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    #[Assert\NotNull]
    private $politicalCommittee;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity=Adherent::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $author;

    /**
     * @var Adherent|null
     *
     * @Gedmo\Blameable(on="create")
     *
     * @ORM\ManyToOne(targetEntity=Adherent::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * @var Adherent|null
     *
     * @Gedmo\Blameable(on="update")
     *
     * @ORM\ManyToOne(targetEntity=Adherent::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $updatedBy;

    /**
     * @var OfficialReportDocument[]|Collection
     *
     * @ORM\OneToMany(targetEntity=OfficialReportDocument::class, mappedBy="report", cascade={"all"})
     */
    private $documents;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->documents = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPoliticalCommittee(): ?PoliticalCommittee
    {
        return $this->politicalCommittee;
    }

    public function setPoliticalCommittee(PoliticalCommittee $politicalCommittee): void
    {
        $this->politicalCommittee = $politicalCommittee;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $author): void
    {
        $this->author = $author;
    }

    public function getCreatedBy(): ?Adherent
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Adherent $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedBy(): ?Adherent
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?Adherent $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function addDocument(OfficialReportDocument $document): void
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }
    }

    public function getLastVersion(): ?int
    {
        return ($doc = $this->getLastDocument()) ? $doc->getVersion() : null;
    }

    public function getLastDocument(): ?OfficialReportDocument
    {
        $document = null;
        $docs = $this->documents->toArray();
        array_walk($docs, function (OfficialReportDocument $doc) use (&$document) {
            $version = $document ? $document->getVersion() : null;
            if ($doc->getVersion() > $version) {
                $document = $doc;
            }
        });

        return $document ?: null;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
