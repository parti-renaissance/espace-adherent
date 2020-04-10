<?php

namespace AppBundle\Donation;

use AppBundle\Csv\CsvResponseFactory;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Utils\PhoneNumberFormatter;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class DonatorExtractCommandHandler
{
    private const CSV_DELIMITER = ';';

    private $adherentRepository;
    private $csvResponseFactory;
    private $translator;

    public function __construct(
        AdherentRepository $adherentRepository,
        CsvResponseFactory $csvResponseFactory,
        TranslatorInterface $translator
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->csvResponseFactory = $csvResponseFactory;
        $this->translator = $translator;
    }

    public function createResponse(DonatorExtractCommand $donatorExtractCommand): Response
    {
        $emails = $donatorExtractCommand->getEmails();
        $fields = $donatorExtractCommand->getFields();

        $csv = Writer::createFromPath('php://temp', 'r+');
        $csv->setDelimiter(self::CSV_DELIMITER);

        $csv->insertOne(array_merge([DonatorExtractCommand::FIELD_EMAIL], $fields));

        foreach ($emails as $email) {
            $row = [
                $this->translateField(DonatorExtractCommand::FIELD_EMAIL) => $email,
            ];

            foreach ($fields as $field) {
                $row[$this->translateField($field)] = null;
            }

            if ($adherent = $this->adherentRepository->findOneByEmail($email)) {
                foreach ($fields as $field) {
                    switch ($field) {
                        case DonatorExtractCommand::FIELD_FIRST_NAME:
                            $row[$this->translateField($field)] = $adherent->getFirstName();

                            break;
                        case DonatorExtractCommand::FIELD_LAST_NAME:
                            $row[$this->translateField($field)] = $adherent->getLastName();

                            break;
                        case DonatorExtractCommand::FIELD_GENDER:
                            $row[$this->translateField($field)] = $adherent->getGender()
                                ? $this->translator->trans(sprintf('common.gender.%s', $adherent->getGender()))
                                : null
                            ;

                            break;
                        case DonatorExtractCommand::FIELD_BIRTH_DATE:
                            $row[$this->translateField($field)] = $adherent->getBirthdate()
                                ? $adherent->getBirthdate()->format('d/m/Y H:i:s')
                                : null
                            ;

                            break;
                        case DonatorExtractCommand::FIELD_NATIONALITY:
                            $row[$this->translateField($field)] = $adherent->getNationality();

                            break;
                        case DonatorExtractCommand::FIELD_PHONE:
                            $row[$this->translateField($field)] = PhoneNumberFormatter::format($adherent->getPhone());

                            break;
                        case DonatorExtractCommand::FIELD_REGISTERED_AT:
                            $row[$this->translateField($field)] = $adherent->getRegisteredAt()
                                ? $adherent->getRegisteredAt()->format('d/m/Y H:i:s')
                                : null
                            ;

                            break;
                        case DonatorExtractCommand::FIELD_COUNTRY:
                            $row[$this->translateField($field)] = $adherent->getCountry();

                            break;
                        case DonatorExtractCommand::FIELD_POSTAL_CODE:
                            $row[$this->translateField($field)] = $adherent->getPostalCode();

                            break;
                        case DonatorExtractCommand::FIELD_ADDRESS:
                            $row[$this->translateField($field)] = $adherent->getInlineFormattedAddress();

                            break;
                        default:
                            break;
                    }
                }
            }

            $csv->insertOne($row);
        }

        return $this->csvResponseFactory->createStreamedResponse($csv);
    }

    private function translateField(string $field): string
    {
        return $this->translator->trans("donator.extract.field.$field");
    }
}
