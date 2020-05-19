<?php

namespace App\Donation;

use App\Csv\CsvResponseFactory;
use App\Entity\Donator;
use App\Extract\AbstractEmailExtractCommandHandler;
use App\Repository\DonatorRepository;
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

        foreach ($fields as $field) {
            switch ($field) {
                case DonatorExtractCommand::FIELD_FIRST_NAME:
                    $row[$this->translateField($field)] = $donator->getFirstName();

                    break;
                case DonatorExtractCommand::FIELD_LAST_NAME:
                    $row[$this->translateField($field)] = $donator->getLastName();

                    break;
                case DonatorExtractCommand::FIELD_GENDER:
                    $row[$this->translateField($field)] = $donator->getGender()
                        ? $this->translator->trans(sprintf('common.gender.%s', $donator->getGender()))
                        : null
                    ;

                    break;
                case DonatorExtractCommand::FIELD_CITY:
                    $row[$this->translateField($field)] = $donator->getCity();

                    break;
                case DonatorExtractCommand::FIELD_COUNTRY:
                    $row[$this->translateField($field)] = $donator->getCountry();

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
