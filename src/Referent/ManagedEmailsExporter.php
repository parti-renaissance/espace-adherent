<?php

namespace AppBundle\Referent;

use AppBundle\Entity\EmailLog;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManagedEmailsExporter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

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
