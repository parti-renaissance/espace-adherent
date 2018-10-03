<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\JeMarcheReport;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class JeMarcheReportMail extends TransactionalMail
{
    const SUBJECT = 'Merci pour votre compte-rendu d\'action.';

    public static function createRecipientFor(JeMarcheReport $jeMarcheReport): RecipientInterface
    {
        return new Recipient($jeMarcheReport->getEmailAddress());
    }

    public static function createTemplateVarsFrom(JeMarcheReport $jeMarcheReport): array
    {
        return [
            'nombre_emails_convaincus' => $jeMarcheReport->countConvinced(),
            'nombre_emails_indecis' => $jeMarcheReport->countAlmostConvinced(),
            'emails_collected_convaincus' => $jeMarcheReport->getConvincedList(', '),
            'emails_collected_indecis' => $jeMarcheReport->getAlmostConvincedList(', '),
        ];
    }
}
