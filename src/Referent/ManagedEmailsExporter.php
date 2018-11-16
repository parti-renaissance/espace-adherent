<?php

namespace AppBundle\Referent;

use AppBundle\Entity\EmailLog;

class ManagedEmailsExporter
{
    /**
     * @param EmailLog[] $managedEmails
     */
    public function exportAsJson(array $managedEmails): string
    {
        $data = [];

        foreach ($managedEmails as $email) {
            $data[] = [
                'subject' => $email->getSubject(),
                'body' => $email->getBody(),
                'recipientsNumber' => $email->getRecipientsNumber(),
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
