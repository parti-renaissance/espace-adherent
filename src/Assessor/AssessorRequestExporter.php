<?php

namespace App\Assessor;

use App\Entity\AssessorRequest;
use App\Utils\PhoneNumberUtils;
use Sonata\Exporter\Exporter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssessorRequestExporter
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Exporter $exporter
    ) {
    }

    public function export(array $assessorRequests): Response
    {
        return $this->exporter->getResponse(
            'xlsx',
            'bureaux-de-vote-demandes-assesseurs',
            new \ArrayIterator(array_map(function (array $row) {
                $columns = [
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
                ];
                $tmp = [];
                foreach ($row as $key => $value) {
                    if (isset($columns[$key])) {
                        $tmp[$columns[$key]] = $value;
                    }
                }

                return $tmp;
            }, $this->prepareData($assessorRequests))),
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
                'votePlaceCity' => $votePlace->getCityName(),
                'votePlaceName' => $votePlace->name,
                'votePlaceAddress' => $votePlace->getAddress(),
                'votePlaceCountry' => Countries::getName($votePlace->getCountry()),
                'officeName' => $this->translator->trans($assessorRequest->getOfficeName()),
                'gender' => $this->translator->trans($assessorRequest->getGenderName()),
                'firstName' => $assessorRequest->getFirstName(),
                'lastName' => $assessorRequest->getLastName(),
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
