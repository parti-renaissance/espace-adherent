<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectCommentRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProjectComment
{
    use EntityIdentityTrait;
    use AuthoredTrait;

    /**
     * @var CitizenProject
     *
     * @ORM\ManyToOne(targetEntity="CitizenProject")
     * @ORM\JoinColumn(nullable=false)
     */
    private $citizenProject;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(
        ?UuidInterface $uuid,
        CitizenProject $citizenProject,
        Adherent $author,
        string $content,
        string $createdAt = 'now'
    ) {
        if (empty($content)) {
            throw new \InvalidArgumentException('Comment content cannot be empty');
        }

        if (null === $author->getCitizenProjectMembershipFor($citizenProject)) {
            throw new \InvalidArgumentException('Only members of the CitizenProject can comment');
        }

        if (!$citizenProject->isApproved()) {
            throw new \InvalidArgumentException('The citizen project is not approved yet');
        }

        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->citizenProject = $citizenProject;
        $this->author = $author;
        $this->content = $content;
        $this->createdAt = new \DateTimeImmutable($createdAt);
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        if ($this->createdAt instanceof \DateTime) {
            $this->createdAt = \DateTimeImmutable::createFromMutable($this->createdAt);
        }

        return $this->createdAt;
    }
}
