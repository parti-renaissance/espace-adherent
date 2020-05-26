<?php

namespace App\Adherent;

use App\Csv\CsvResponseFactory;
use App\Extract\AbstractEmailExtractCommandHandler;
use App\Repository\AdherentRepository;
use App\Utils\PhoneNumberFormatter;
use Symfony\Component\Translation\TranslatorInterface;

class AdherentExtractCommandHandler extends AbstractEmailExtractCommandHandler
{
    private $adherentRepository;

    public function __construct(
        CsvResponseFactory $csvResponseFactory,
        TranslatorInterface $translator,
        AdherentRepository $adherentRepository
    ) {
        parent::__construct($csvResponseFactory, $translator);

        $this->adherentRepository = $adherentRepository;
    }

    protected function computeRow(array $row, string $email, array $fields): array
    {
        if (!$adherent = $this->adherentRepository->findOneByEmail($email)) {
            return $row;
        }

        foreach ($fields as $field) {
            switch ($field) {
                case AdherentExtractCommand::FIELD_GENDER:
                    $row[$this->translateField($field)] = $adherent->getGender()
                        ? $this->translator->trans(sprintf('common.gender.%s', $adherent->getGender()))
                        : null
                    ;

                    break;
                case AdherentExtractCommand::FIELD_FIRST_NAME:
                    $row[$this->translateField($field)] = $adherent->getFirstName();

                    break;
                case AdherentExtractCommand::FIELD_LAST_NAME:
                    $row[$this->translateField($field)] = $adherent->getLastName();

                    break;
                case AdherentExtractCommand::FIELD_ADDRESS:
                    $row[$this->translateField($field)] = $adherent->getAddress();

                    break;
                case AdherentExtractCommand::FIELD_POSTAL_CODE:
                    $row[$this->translateField($field)] = $adherent->getPostalCode();

                    break;
                case AdherentExtractCommand::FIELD_CITY:
                    $row[$this->translateField($field)] = $adherent->getCityName();

                    break;
                case AdherentExtractCommand::FIELD_COUNTRY:
                    $row[$this->translateField($field)] = $adherent->getCountry();

                    break;
                case AdherentExtractCommand::FIELD_NATIONALITY:
                    $row[$this->translateField($field)] = $adherent->getNationality();

                    break;
                case AdherentExtractCommand::FIELD_PHONE:
                    $row[$this->translateField($field)] = PhoneNumberFormatter::format($adherent->getPhone());

                    break;
                case AdherentExtractCommand::FIELD_REGISTERED_AT:
                    $row[$this->translateField($field)] = $adherent->getRegisteredAt()
                        ? $adherent->getRegisteredAt()->format('d/m/Y H:i:s')
                        : null
                    ;

                    break;
                case AdherentExtractCommand::FIELD_BIRTH_DATE:
                    $row[$this->translateField($field)] = $adherent->getBirthdate()
                        ? $adherent->getBirthdate()->format('d/m/Y H:i:s')
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
        return 'adherent.extract.field.';
    }
}
