<?php

namespace App\Exporter;

use App\Entity\AssessorOfficeEnum;
use App\Entity\AssessorRequest;
use App\Entity\VotePlace;
use App\Serializer\XlsxEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;

class CityAssessorExporter
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
                    'substituteLastName' => 'Nom assesseur suppléant',
                    'substituteFirstName' => 'Prénom assesseur suppléant',
                    'substituteBirthdate' => 'Date de naissance assesseur suppléant',
                    'substituteAddress' => 'Adresse postale assesseur suppléant',
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

        foreach ($votePlaces as $votePlace) {
            $votePlaceData = [
               'votePlaceId' => $votePlace->getId(),
               'votePlaceName' => $votePlace->getName().' '.$votePlace->getCode(),
               'votePlaceAddress' => $votePlace->getAddress().', '.$votePlace->getPostalCode().' '.$votePlace->getCity().' '.$votePlace->getCountry(),
            ];

            $holder = [
                'holderLastName' => null,
                'holderFirstName' => null,
                'holderBirthdate' => null,
                'holderAddress' => null,
            ];

            $substitute = [
                'substituteLastName' => null,
                'substituteFirstName' => null,
                'substituteBirthdate' => null,
                'substituteAddress' => null,
            ];

            /** @var AssessorRequest $assessorRequest */
            foreach ($votePlace->getAssessorRequests() as $assessorRequest) {
                if (AssessorOfficeEnum::HOLDER === $assessorRequest->getOffice()) {
                    $holder = [
                        'holderLastName' => $assessorRequest->getLastName(),
                        'holderFirstName' => $assessorRequest->getFirstName(),
                        'holderBirthdate' => $assessorRequest->getBirthdate()->format('d/m/Y'),
                        'holderAddress' => $assessorRequest->getAddress().', '.$assessorRequest->getPostalCode().' '.$assessorRequest->getCity(),
                    ];
                } elseif (AssessorOfficeEnum::SUBSTITUTE === $assessorRequest->getOffice()) {
                    $substitute = [
                        'substituteLastName' => $assessorRequest->getLastName(),
                        'substituteFirstName' => $assessorRequest->getFirstName(),
                        'substituteBirthdate' => $assessorRequest->getBirthdate()->format('d/m/Y'),
                        'substituteAddress' => $assessorRequest->getAddress().', '.$assessorRequest->getPostalCode().' '.$assessorRequest->getCity(),
                    ];
                }
            }

            $data[] = array_merge($votePlaceData, $holder, $substitute);
        }

        return $data;
    }
}
