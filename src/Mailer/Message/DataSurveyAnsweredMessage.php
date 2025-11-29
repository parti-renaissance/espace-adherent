<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Jecoute\JemarcheDataSurvey;
use Ramsey\Uuid\Uuid;

final class DataSurveyAnsweredMessage extends Message
{
    public static function create(JemarcheDataSurvey $dataSurvey): self
    {
        return new self(
            Uuid::uuid4(),
            $dataSurvey->getEmailAddress(),
            null,
            'Votre adhésion à La République En Marche !',
            ['first_name' => (string) $dataSurvey->getFirstName()]
        );
    }
}
