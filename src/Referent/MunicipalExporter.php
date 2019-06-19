<?php

namespace AppBundle\Referent;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Repository\AdherentRepository;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MunicipalExporter
{
    private $urlGenerator;
    private $adherentRepository;
    private $phoneUtils;

    public function __construct(UrlGeneratorInterface $urlGenerator, AdherentRepository $adherentRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->adherentRepository = $adherentRepository;
        $this->phoneUtils = PhoneNumberUtil::getInstance();
    }

    /**
     * @param RunningMateRequest[] $runningMates
     */
    public function exportRunningMateAsJson(array $runningMates, string $detailRoute): string
    {
        $data = [];

        /** @var RunningMateRequest $runningMate */
        foreach ($runningMates as $i => $runningMate) {
            $data[] = [
                'lastName' => $runningMate->getLastName(),
                'firstName' => $runningMate->getFirstName(),
                'phone' => $runningMate->getPhone() instanceof PhoneNumber
                    ? $this->phoneUtils->format($runningMate->getPhone(), PhoneNumberFormat::INTERNATIONAL)
                    : '',
                'favoriteCities' => implode(', ', FranceCitiesBundle::searchCitiesByInseeCodes($runningMate->getFavoriteCities())),
                'cvLink' => [
                    'label' => 'Télécharger le CV',
                    'url' => $this->urlGenerator->generate('asset_url', [
                        'path' => $runningMate->getPathWithDirectory(),
                        'mime_type' => 'application/pdf',
                    ]),
                ],
                'isAdherent' => $runningMate->isAdherent() ? 'Oui' : 'Non',
                'detailLink' => [
                    'label' => "<span id='application-detail-$i' class='btn btn--default'>Voir le détail</span>",
                    'url' => $this->urlGenerator->generate($detailRoute, [
                        'uuid' => $runningMate->getUuid(),
                    ]),
                ],
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }

    /**
     * @param VolunteerRequest[] $volunteers
     */
    public function exportVolunteerAsJson(array $volunteers, string $detailRoute): string
    {
        $data = [];

        /** @var VolunteerRequest $volunteer */
        foreach ($volunteers as $i => $volunteer) {
            $data[] = [
                'lastName' => $volunteer->getLastName(),
                'firstName' => $volunteer->getFirstName(),
                'phone' => $volunteer->getPhone() instanceof PhoneNumber
                    ? $this->phoneUtils->format($volunteer->getPhone(), PhoneNumberFormat::INTERNATIONAL)
                    : '',
                'favoriteCities' => implode(', ', FranceCitiesBundle::searchCitiesByInseeCodes($volunteer->getFavoriteCities())),
                'isAdherent' => $volunteer->isAdherent() ? 'Oui' : 'Non',
                'detailLink' => [
                    'label' => "<span id='application-detail-$i' class='btn btn--default'>Voir le détail</span>",
                    'url' => $this->urlGenerator->generate($detailRoute, [
                        'uuid' => $volunteer->getUuid(),
                    ]),
                ],
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
