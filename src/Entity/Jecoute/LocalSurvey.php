<?php

namespace AppBundle\Entity\Jecoute;

use AppBundle\Jecoute\SurveyTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class LocalSurvey extends Survey
{
    public function getType(): string
    {
        return SurveyTypeEnum::LOCAL;
    }
}
