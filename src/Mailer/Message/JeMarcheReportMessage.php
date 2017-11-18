<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\JeMarcheReport;
use Ramsey\Uuid\Uuid;

final class JeMarcheReportMessage extends Message
{
    public static function createFromJeMarcheReport(JeMarcheReport $jeMarcheReport): self
    {
        return new self(
            Uuid::uuid4(),
            $jeMarcheReport->getEmailAddress(),
            null,
            self::getTemplateVars(
                $jeMarcheReport->countConvinced(),
                $jeMarcheReport->countAlmostConvinced(),
                $jeMarcheReport->getConvincedList(', '),
                $jeMarcheReport->getAlmostConvincedList(', ')
            )
        );
    }

    private static function getTemplateVars(
        int $countConvinced,
        int $countAlmostConvinced,
        string $convicedList,
        string $almostConvincedList
    ): array {
        return [
            'nombre_emails_convaincus' => $countConvinced,
            'nombre_emails_indecis' => $countAlmostConvinced,
            'emails_collected_convaincus' => $convicedList,
            'emails_collected_indecis' => $almostConvincedList,
        ];
    }
}
