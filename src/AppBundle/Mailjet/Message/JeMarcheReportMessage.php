<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\JeMarcheReport;
use Ramsey\Uuid\Uuid;

final class JeMarcheReportMessage extends MailjetMessage
{
    public static function createFromJeMarcheReport(JeMarcheReport $jeMarcheReport): self
    {
        return new static(
            Uuid::uuid4(),
            '133783',
            $jeMarcheReport->getEmailAddress(),
            null,
            'Merci pour votre compte-rendu d\'action.',
            static::getTemplateVars($jeMarcheReport)
        );
    }

    private static function getTemplateVars(JeMarcheReport $jeMarcheReport): array
    {
        return [
            'nombre_emails_convaincus' => $jeMarcheReport->countConvinced(),
            'nombre_emails_indecis' => $jeMarcheReport->countAlmostConvinced(),
            'emails_collected_convaincus' => $jeMarcheReport->getConvincedList(', '),
            'emails_collected_indecis' => $jeMarcheReport->getAlmostConvincedList(', '),
        ];
    }
}
