<?php

namespace App\Exporter;

use App\Entity\AssessorOfficeEnum;
use App\Entity\AssessorRequest;
use App\Entity\Election\VotePlace;
use App\Serializer\XlsxEncoder;
use App\Utils\PhoneNumberUtils;

class CityAssessorExporter
{
    public function export(array $votePlaces): string
    {
        return (new XlsxEncoder())->encode(
            $this->prepareData($votePlaces),
            XlsxEncoder::FORMAT,
            [
                XlsxEncoder::HEADERS_KEY => [
                    'votePlaceId' => 'Numéro du bureau de vote',
                    'votePlaceName' => 'Nom du bureau de vote',
                    'votePlaceAddress' => 'Adresse postale du bureau de vote',
                    'holderLastName' => 'Nom assesseur titulaire',
                    'holderFirstName' => 'Prénom assesseur titulaire',
                    'holderBirthdate' => 'Date de naissance assesseur titulaire',
                    'holderAddress' => 'Adresse postale assesseur titulaire',
                    'holderPhoneNumber' => 'Numéro de téléphone assesseur titulaire',
                    'holderVoterNumber' => 'Numéro d\'électeur assesseur titulaire',
                    'substituteLastName' => 'Nom assesseur suppléant',
                    'substituteFirstName' => 'Prénom assesseur suppléant',
                    'substituteBirthdate' => 'Date de naissance assesseur suppléant',
                    'substituteAddress' => 'Adresse postale assesseur suppléant',
                    'substitutePhoneNumber' => 'Numéro de téléphone assesseur suppléant',
                    'substituteVoterNumber' => 'Numéro d\'électeur assesseur suppléant',
                ],
            ]
        );
    }

    /**
     * @param VotePlace[] $votePlaces
     */
    private function prepareData(array $votePlaces): array
    {
        $data = [];

        foreach ($votePlaces as $row) {
            $votePlace = $row['vote_place'];
            $votePlaceData = [
               'votePlaceId' => $votePlace->getId(),
               'votePlaceName' => $votePlace->name.' '.$votePlace->code,
               'votePlaceAddress' => $votePlace->getAddress().', '.$votePlace->getPostalCode().' '.$votePlace->getCityName().' '.$votePlace->getCountry(),
            ];

            $holder = [
                'holderLastName' => null,
                'holderFirstName' => null,
                'holderBirthdate' => null,
                'holderAddress' => null,
                'holderPhoneNumber' => null,
                'holderVoterNumber' => null,
            ];

            $substitute = [
                'substituteLastName' => null,
                'substituteFirstName' => null,
                'substituteBirthdate' => null,
                'substituteAddress' => null,
                'substitutePhoneNumber' => null,
                'substituteVoterNumber' => null,
            ];

            /** @var AssessorRequest $assessorRequest */
            foreach ($row['assessors'] as $assessorRequest) {
                if (AssessorOfficeEnum::HOLDER === $assessorRequest->getOffice()) {
                    $holder = [
                        'holderLastName' => $assessorRequest->getLastName(),
                        'holderFirstName' => $assessorRequest->getFirstName(),
                        'holderBirthdate' => $assessorRequest->getBirthdate()->format('d/m/Y'),
                        'holderAddress' => $assessorRequest->getAddress().', '.$assessorRequest->getPostalCode().' '.$assessorRequest->getCity().' '.$assessorRequest->getCountry(),
                        'holderPhoneNumber' => PhoneNumberUtils::format($assessorRequest->getPhone()),
                        'holderVoterNumber' => $assessorRequest->getVoterNumber(),
                    ];
                } elseif (AssessorOfficeEnum::SUBSTITUTE === $assessorRequest->getOffice()) {
                    $substitute = [
                        'substituteLastName' => $assessorRequest->getLastName(),
                        'substituteFirstName' => $assessorRequest->getFirstName(),
                        'substituteBirthdate' => $assessorRequest->getBirthdate()->format('d/m/Y'),
                        'substituteAddress' => $assessorRequest->getAddress().', '.$assessorRequest->getPostalCode().' '.$assessorRequest->getCity().' '.$assessorRequest->getCountry(),
                        'substitutePhoneNumber' => PhoneNumberUtils::format($assessorRequest->getPhone()),
                        'substituteVoterNumber' => $assessorRequest->getVoterNumber(),
                    ];
                }
            }

            $data[] = array_merge($votePlaceData, $holder, $substitute);
        }

        return $data;
    }
}
