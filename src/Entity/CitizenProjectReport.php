<?php

namespace AppBundle\Entity;

use AppBundle\Report\ReportType;
use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProjectReport extends Report
{
    /**
     * @var CitizenProject
     *
     * @ORM\ManyToOne(targetEntity="CitizenProject")
     * @ORM\JoinColumn(name="citizen_project_id")
     */
    private $subject;

    public function __construct(CitizenProject $citizenProject, Adherent $author, array $reasons, ?string $comment)
    {
        parent::__construct($author, $reasons, $comment);
        $this->subject = $citizenProject;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectType(): string
    {
        return ReportType::CITIZEN_PROJECT;
    }
}
