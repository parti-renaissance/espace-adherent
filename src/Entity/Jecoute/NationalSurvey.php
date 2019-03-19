<?php

namespace AppBundle\Entity\Jecoute;

use AppBundle\Entity\Administrator;
use AppBundle\Jecoute\SurveyTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Jecoute\NationalSurveyRepository")
 */
class NationalSurvey extends Survey
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $administrator;

    public function __construct(Administrator $administrator, string $name = null, bool $published = false)
    {
        $this->administrator = $administrator;

        parent::__construct($name, $published);
    }

    public function setAdministrator(Administrator $administrator): void
    {
        $this->administrator = $administrator;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function getType(): string
    {
        return SurveyTypeEnum::NATIONAL;
    }
}
