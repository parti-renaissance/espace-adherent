<?php

namespace App\Entity\Jecoute;

use App\Entity\Administrator;
use App\Jecoute\SurveyTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\NationalSurveyRepository")
 */
class NationalSurvey extends Survey
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $administrator;

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
