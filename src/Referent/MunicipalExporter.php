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

    public function __construct(UrlGeneratorInterface $urlGenerator, AdherentRepository $adherentRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->adherentRepository = $adherentRepository;
    }

    /**
     * @param RunningMateRequest[] $runningMates
     */
    public function exportRunningMateAsJson(array $runningMates): string
    {
        $data = [];
        $phoneUtils = PhoneNumberUtil::getInstance();

        /** @var RunningMateRequest $runningMate */
        foreach ($runningMates as $runningMate) {
            $data[] = [
                'lastName' => $runningMate->getLastName(),
                'firstName' => $runningMate->getFirstName(),
                'phone' => $runningMate->getPhone() instanceof PhoneNumber ? $phoneUtils->format($runningMate->getPhone(), PhoneNumberFormat::INTERNATIONAL) : '',
                'favoriteCities' => $runningMate->getFavoriteCities(),
                'cvLink' => [
                    'label' => 'Télécharger le CV',
                    'url' => $this->urlGenerator->generate('asset_url', [
                        'path' => $runningMate->getPathWithDirectory(),
                        'mime_type' => 'application/pdf',
                    ]),
                ],
                'isAdherent' => $runningMate->isAdherent() ? 'Oui' : 'Non',
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }

    /**
     * @param VolunteerRequest[] $volunteers
     */
    public function exportVolunteerAsJson(array $volunteers): string
    {
        $data = [];

        /** @var VolunteerRequest $volunteer */
        foreach ($volunteers as $volunteer) {
            $data[] = [
                'lastName' => $volunteer->getLastName(),
                'firstName' => $volunteer->getFirstName(),
                'phone' => $volunteer->getPhone(),
                'favoriteCities' => $volunteer->getFavoriteCities(),
                'isAdherent' => $volunteer->isAdherent() ? 'Oui' : 'Non',
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
