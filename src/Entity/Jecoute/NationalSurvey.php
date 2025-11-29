<?php

declare(strict_types=1);

namespace App\Entity\Jecoute;

use App\Jecoute\SurveyTypeEnum;
use App\Repository\Jecoute\NationalSurveyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NationalSurveyRepository::class)]
class NationalSurvey extends Survey
{
    public function getType(): string
    {
        return SurveyTypeEnum::NATIONAL;
    }
}
