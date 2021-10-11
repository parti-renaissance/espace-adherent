<?php

namespace App\Entity\Jecoute;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait DataSurveyAwareTrait
{
    /**
     * @var DataSurvey|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Jecoute\DataSurvey", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    private $dataSurvey;

    public function getDataSurvey(): ?DataSurvey
    {
        return $this->dataSurvey;
    }

    public function setDataSurvey(DataSurvey $dataSurvey): void
    {
        $this->dataSurvey = $dataSurvey;
    }
}
