<?php

namespace App\Entity\Jecoute;

use App\Jecoute\SurveyTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\NationalSurveyRepository")
 */
class NationalSurvey extends Survey
{
    public function getType(): string
    {
        return SurveyTypeEnum::NATIONAL;
    }
}
