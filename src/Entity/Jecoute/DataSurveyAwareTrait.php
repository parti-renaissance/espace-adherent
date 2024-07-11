<?php

namespace App\Entity\Jecoute;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait DataSurveyAwareTrait
{
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: DataSurvey::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    private ?DataSurvey $dataSurvey = null;

    public function getDataSurvey(): ?DataSurvey
    {
        return $this->dataSurvey;
    }

    public function setDataSurvey(DataSurvey $dataSurvey): void
    {
        $this->dataSurvey = $dataSurvey;
    }
}
