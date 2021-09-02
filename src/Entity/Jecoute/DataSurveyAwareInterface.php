<?php

namespace App\Entity\Jecoute;

interface DataSurveyAwareInterface
{
    public function getDataSurvey(): ?DataSurvey;

    public function setDataSurvey(DataSurvey $dataSurvey): void;
}
