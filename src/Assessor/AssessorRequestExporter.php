<?php

namespace App\Assessor;

use App\Entity\AssessorRequest;
use App\Serializer\XlsxEncoder;
use App\Utils\PhoneNumberUtils;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssessorRequestExporter
{
    public const FILE_NAME = 'bureaux-de-vote-demandes-assesseurs';

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function export(array $assessorRequests): string
    {
        $encoder = new XlsxEncoder();

        return $encoder->encode(
            $this->prepareData($assessorRequests),
            XlsxEncoder::FORMAT,
            [
                XlsxEncoder::HEADERS_KEY => [
                    'votePlaceCity' => 'Ville du bureau de vote',
                    'votePlaceName' => 'Nom du bureau vote',
                    'votePlaceAddress' => 'Adresse du bureau de vote',
                    'votePlaceCountry' => 'Pays du bureau de vote',
                    'officeName' => 'Fonction',
                    'gender' => 'Genre',
                    'lastName' => 'Nom',
                    'firstName' => 'Prénoms',
                    'birthdate' => 'Date de naissance',
                    'birthCity' => 'Lieu de naissance',
                    'address' => 'Adresse',
                    'postalCode' => 'Code postal',
                    'city' => 'Ville',
                    'officeNumber' => "BV d'inscription sur les listes",
                    'voteCity' => "Ville du BV d'inscription sur les listes",
                    'emailAddress' => 'Adresse email',
                    'formattedPhone' => 'Téléphone',
                ],
            ]
        );
    }

    /**
     * @param AssessorRequest[] $assessorRequests
     */
    private function prepareData(array $assessorRequests): array
    {
        $data = [];

        foreach ($assessorRequests as $assessorRequest) {
            $votePlace = $assessorRequest->getVotePlace();

            $data[] = [
                'votePlaceCity' => $votePlace->getCity(),
                'votePlaceName' => $votePlace->getName(),
                'votePlaceAddress' => $votePlace->getAddress(),
                'votePlaceCountry' => Countries::getName($votePlace->getCountry()),
                'officeName' => $this->translator->trans($assessorRequest->getOfficeName()),
                'lastName' => $assessorRequest->getLastName(),
                'firstName' => $assessorRequest->getFirstName(),
                'gender' => $this->translator->trans($assessorRequest->getGenderName()),
                'birthdate' => $assessorRequest->getBirthdate()->format('d/m/Y'),
                'birthCity' => $assessorRequest->getBirthCity(),
                'address' => $assessorRequest->getAddress(),
                'postalCode' => $assessorRequest->getPostalCode(),
                'city' => $assessorRequest->getCity(),
                'voteCity' => $assessorRequest->getVoteCity(),
                'officeNumber' => $assessorRequest->getOfficeNumber(),
                'emailAddress' => $assessorRequest->getEmailAddress(),
                'formattedPhone' => PhoneNumberUtils::format($assessorRequest->getPhone()),
            ];
        }

        return $data;
    }
}
