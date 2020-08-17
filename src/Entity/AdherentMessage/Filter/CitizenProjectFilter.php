<?php

namespace App\Entity\AdherentMessage\Filter;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\CitizenProject;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProjectFilter extends AbstractAdherentMessageFilter implements AdherentSegmentAwareFilterInterface
{
    use AdherentSegmentAwareFilterTrait;

    /**
     * @var CitizenProject
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CitizenProject")
     *
     * @Assert\NotBlank
     */
    private $citizenProject;

    public function __construct(CitizenProject $citizenProject = null)
    {
        $this->citizenProject = $citizenProject;
    }

    public function getCitizenProject(): ?CitizenProject
    {
        return $this->citizenProject;
    }

    public function setCitizenProject(CitizenProject $citizenProject): void
    {
        $this->citizenProject = $citizenProject;
    }
}
