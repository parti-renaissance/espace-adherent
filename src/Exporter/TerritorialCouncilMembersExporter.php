<?php

namespace App\Exporter;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use App\TerritorialCouncil\Filter\MembersListFilter;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class TerritorialCouncilMembersExporter
{
    private $exporter;
    private $repository;
    private $translator;

    public function __construct(
        SonataExporter $exporter,
        TerritorialCouncilMembershipRepository $repository,
        TranslatorInterface $translator
    ) {
        $this->exporter = $exporter;
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function getResponse(string $format, MembersListFilter $filter): Response
    {
        $array = new \ArrayObject($this->repository->getForExport($filter));

        return $this->exporter->getResponse(
            $format,
            sprintf('coTerr-membres--%s.%s', date('d-m-Y--H-i'), $format),
            new IteratorCallbackSourceIterator(
                $array->getIterator(),
                function (TerritorialCouncilMembership $tcMembership) {
                    $adherent = $tcMembership->getAdherent();

                    return [
                        'Nom' => $adherent->getLastName(),
                        'Prénom' => $adherent->getFirstName(),
                        'Genre' => $this->translator->trans($adherent->getGenderName()),
                        'Âge' => $adherent->getAge(),
                        'Qualités' => $this->getQualityNames($tcMembership, $adherent->isFemale()),
                        'Zones géographiques' => $tcMembership->getQualityZonesAsString(),
                        'Membre depuis le' => $tcMembership->getJoinedAt()->format('d/m/Y'),
                    ];
                },
            )
        );
    }

    private function getQualityNames(TerritorialCouncilMembership $tcMembership, bool $isFemale): string
    {
        return implode(', ', \array_map(function (TerritorialCouncilQuality $quality) use ($isFemale) {
            return $this->translator->trans('territorial_council.membership.quality.'.$quality->getName(), [], null, null, (int) $isFemale);
        }, $tcMembership->getQualities()->toArray()));
    }
}
