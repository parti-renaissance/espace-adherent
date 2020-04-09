<?php

namespace AppBundle\Donation;

use AppBundle\Csv\CsvResponseFactory;
use AppBundle\Repository\AdherentRepository;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\Response;

class DonatorExtractCommandHandler
{
    private const CSV_DELIMITER = ';';

    private $adherentRepository;
    private $csvResponseFactory;

    public function __construct(AdherentRepository $adherentRepository, CsvResponseFactory $csvResponseFactory)
    {
        $this->adherentRepository = $adherentRepository;
        $this->csvResponseFactory = $csvResponseFactory;
    }

    public function createResponse(DonatorExtractCommand $donatorExtractCommand): Response
    {
        $emails = $donatorExtractCommand->getEmails();
        $fields = $donatorExtractCommand->getFields();

        $csv = Writer::createFromPath('php://temp', 'r+');
        $csv->setDelimiter(self::CSV_DELIMITER);

        $csv->insertOne(array_merge([DonatorExtractCommand::FIELD_EMAIL], $fields));

        $rows = [];
        foreach ($emails as $email) {
            $row = [DonatorExtractCommand::FIELD_EMAIL => $email];
            foreach ($fields as $field) {
                $row[$field] = null;
            }

            if ($adherent = $this->adherentRepository->findOneByEmail($email)) {
                foreach ($fields as $field) {
                    switch ($field) {
                        case DonatorExtractCommand::FIELD_FIRST_NAME:
                            $row[$field] = $adherent->getFirstName();

                            break;
                        case DonatorExtractCommand::FIELD_LAST_NAME:
                            $row[$field] = $adherent->getLastName();

                            break;
                        case DonatorExtractCommand::FIELD_PHONE:
                            $row[$field] = $adherent->getPhone() ? $adherent->getPhone()->getNationalNumber() : null;

                            break;
                        case DonatorExtractCommand::FIELD_REGISTERED_AT:
                            $row[$field] = $adherent->getRegisteredAt()->format('Y/m/d H:i:s');

                            break;
                        default:
                            break;
                    }
                }
            }

            $csv->insertOne($row);
            $rows[] = $row;
        }

        return $this->csvResponseFactory->createStreamedResponse($csv);
    }
}
