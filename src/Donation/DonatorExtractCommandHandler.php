<?php

namespace App\Donation;

use App\Csv\CsvResponseFactory;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Extract\AbstractEmailExtractCommandHandler;
use App\Repository\DonatorRepository;
use App\Utils\PhoneNumberFormatter;
use Symfony\Component\Translation\TranslatorInterface;

class DonatorExtractCommandHandler extends AbstractEmailExtractCommandHandler
{
    private $donatorRepository;

    public function __construct(
        CsvResponseFactory $csvResponseFactory,
        TranslatorInterface $translator,
        DonatorRepository $donatorRepository
    ) {
        parent::__construct($csvResponseFactory, $translator);

        $this->donatorRepository = $donatorRepository;
    }

    protected function computeRow(array $row, string $email, array $fields): array
    {
        /** @var Donator $donator */
        if (!$donator = $this->donatorRepository->findOneBy(['emailAddress' => $email])) {
            return $row;
        }

        /** @var Donation|null $lastDonation */
        $lastDonation = $donator->getDonations()->first();

        foreach ($fields as $field) {
            switch ($field) {
                case DonatorExtractCommand::FIELD_GENDER:
                    $row[$this->translateField($field)] = $donator->getGender()
                        ? $this->translator->trans(sprintf('common.gender.%s', $donator->getGender()))
                        : null
                    ;

                    break;
                case DonatorExtractCommand::FIELD_FIRST_NAME:
                    $row[$this->translateField($field)] = $donator->getFirstName();

                    break;
                case DonatorExtractCommand::FIELD_LAST_NAME:
                    $row[$this->translateField($field)] = $donator->getLastName();

                    break;
                case DonatorExtractCommand::FIELD_ADDRESS:
                    $row[$this->translateField($field)] = $lastDonation
                        ? $lastDonation->getAddress()
                        : null
                    ;

                    break;
                case DonatorExtractCommand::FIELD_POSTAL_CODE:
                    $row[$this->translateField($field)] = $lastDonation
                        ? $lastDonation->getPostalCode()
                        : null
                    ;

                    break;
                case DonatorExtractCommand::FIELD_CITY:
                    $row[$this->translateField($field)] = $lastDonation
                        ? $lastDonation->getCityName()
                        : null
                    ;

                    break;
                case DonatorExtractCommand::FIELD_COUNTRY:
                    $row[$this->translateField($field)] = $lastDonation
                        ? $lastDonation->getCountry()
                        : null
                    ;

                    break;
                case DonatorExtractCommand::FIELD_NATIONALITY:
                    $row[$this->translateField($field)] = $lastDonation
                        ? $lastDonation->getNationality()
                        : null
                    ;

                    break;
                case DonatorExtractCommand::FIELD_PHONE:
                    $row[$this->translateField($field)] = $lastDonation
                        ? PhoneNumberFormatter::format($lastDonation->getPhone())
                        : null
                    ;

                    break;
                default:
                    break;
            }
        }

        return $row;
    }

    public static function getTranslationPrefix(): string
    {
        return 'donator.extract.field.';
    }
}
