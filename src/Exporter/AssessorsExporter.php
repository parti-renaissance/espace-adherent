<?php

namespace App\Exporter;

use App\Assessor\Filter\AssessorRequestExportFilter;
use App\Entity\AssessorRequest;
use App\Entity\VotePlace;
use App\Repository\AssessorRequestRepository;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class AssessorsExporter
{
    private $exporter;
    private $repository;
    private $translator;

    public function __construct(
        SonataExporter $exporter,
        AssessorRequestRepository $repository,
        TranslatorInterface $translator
    ) {
        $this->exporter = $exporter;
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function getResponse(string $format, AssessorRequestExportFilter $filter): Response
    {
        return $this->exporter->getResponse(
            $format,
            sprintf('assesseurs--%s.%s', date('d-m-Y--H-i'), $format),
            new IteratorCallbackSourceIterator(
                $this->repository->getExportQueryBuilder($filter)->iterate(),
                function (array $data) {
                    /** @var AssessorRequest $request */
                    $request = $data[0];

                    return [
                        'Nom' => $request->getLastName(),
                        'Prénom' => $request->getFirstName(),
                        'Email' => $request->getEmailAddress(),
                        'Bureaux de vote souhaités' => implode(' | ',
                            $request->getVotePlaceWishes()->map(
                                static function (VotePlace $place) {
                                    return sprintf('%s (%s)', $place->getName(), $place->getCode());
                                })
                                ->toArray()
                        ),
                        'Nom de naissance' => $request->getBirthName(),
                        'Genre' => $this->translator->trans($request->getGenderName()),
                        'Téléphone' => $request->getPhone()->getNationalNumber(),
                        'Date de naissance' => $request->getBirthdate()->format('d/m/Y'),
                        'Ville de naissance' => $request->getBirthCity(),
                        'Adresse' => $request->getAddress(),
                        'Code postal' => $request->getPostalCode(),
                        'Ville' => $request->getCity(),
                        'Ville de vote' => $request->getVoteCity(),
                        'N° du bureau de vote' => $request->getOfficeNumber(),
                        'Ville souhaitée' => $request->getAssessorCity(),
                        'Code postal de la ville souhaitée' => $request->getAssessorPostalCode(),
                        'Pays souhaité' => $request->getAssessorCountry(),
                        'Rôle' => $this->translator->trans($request->getOfficeName()),
                    ];
                },
            )
        );
    }
}
