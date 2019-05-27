<?php

namespace AppBundle\Jecoute;

use AppBundle\Entity\Jecoute\DataSurvey;
use Symfony\Component\EventDispatcher\Event;

class DataSurveyEvent extends Event
{
    private $dataSurvey;

    public function __construct(DataSurvey $dataSurvey)
    {
        $this->dataSurvey = $dataSurvey;
    }

    public function getDataSurvey(): DataSurvey
    {
        return $this->dataSurvey;
    }
}
