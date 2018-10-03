<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\JeMarcheReport;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class JeMarcheReportMail extends TransactionalMail
{
    public const SUBJECT = 'Merci pour votre compte-rendu d\'action.';

    public static function createRecipient(JeMarcheReport $report): RecipientInterface
    {
        return new Recipient($report->getEmailAddress());
    }

    public static function createTemplateVars(JeMarcheReport $report): array
    {
        return [
            'nombre_emails_convaincus' => $report->countConvinced(),
            'nombre_emails_indecis' => $report->countAlmostConvinced(),
            'emails_collected_convaincus' => $report->getConvincedList(', '),
            'emails_collected_indecis' => $report->getAlmostConvincedList(', '),
        ];
    }
}
