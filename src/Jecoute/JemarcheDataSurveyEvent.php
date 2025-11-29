<?php

declare(strict_types=1);

namespace App\Jecoute;

use App\Entity\Jecoute\JemarcheDataSurvey;
use Symfony\Contracts\EventDispatcher\Event;

class JemarcheDataSurveyEvent extends Event
{
    private $dataSurvey;

    public function __construct(JemarcheDataSurvey $dataSurvey)
    {
        $this->dataSurvey = $dataSurvey;
    }

    public function getDataSurvey(): JemarcheDataSurvey
    {
        return $this->dataSurvey;
    }
}
