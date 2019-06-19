<?php

namespace AppBundle\Referent;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
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
    public function exportRunningMateAsJson(array $runningMates, string $detailRoute, string $tagsEditRoute): string
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
                'favoriteCities' => implode(', ', $runningMate->getFavoriteCitiesNames()),
                'tags' => implode(', ', $runningMate->getTags()->toArray()),
                'cvLink' => [
                    'label' => 'Télécharger le CV',
                    'url' => $this->urlGenerator->generate('asset_url', [
                        'path' => $runningMate->getPathWithDirectory(),
                        'mime_type' => 'application/pdf',
                    ]),
                ],
                'isAdherent' => $runningMate->isAdherent() ? 'Oui' : 'Non',
                'show' => [
                    'label' => "<span id='application-detail-$i' class='btn btn--default'><i class='fa fa-eye'></i></span>",
                    'url' => $this->urlGenerator->generate($detailRoute, [
                        'uuid' => $runningMate->getUuid(),
                    ]),
                ],
                'edit' => [
                    'label' => "<span id='application-edit-$i' class='btn btn--default'><i class='fa fa-edit'></i></span>",
                    'url' => $this->urlGenerator->generate($tagsEditRoute, [
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
    public function exportVolunteerAsJson(array $volunteers, string $detailRoute, string $tagsEditRoute): string
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
                'favoriteCities' => implode(', ', $volunteer->getFavoriteCitiesNames()),
                'tags' => implode(', ', $volunteer->getTags()->toArray()),
                'isAdherent' => $volunteer->isAdherent() ? 'Oui' : 'Non',
                'show' => [
                    'label' => "<span id='application-detail-$i' class='btn btn--default'><i class='fa fa-eye'></i></span>",
                    'url' => $this->urlGenerator->generate($detailRoute, [
                        'uuid' => $volunteer->getUuid(),
                    ]),
                ],
                'edit' => [
                    'label' => "<span id='application-edit-$i' class='btn btn--default'><i class='fa fa-edit'></i></span>",
                    'url' => $this->urlGenerator->generate($tagsEditRoute, [
                        'uuid' => $volunteer->getUuid(),
                    ]),
                ],
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
