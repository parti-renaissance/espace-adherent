<?php

namespace AppBundle\Referent;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Entity\Jecoute\NationalSurvey;
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
            $isAdherent = (bool) $runningMate['isAdherent'];
            $runningMate = $runningMate[0];
            $data[] = [
                'lastName' => $runningMate->getLastName(),
                'firstName' => $runningMate->getFirstName(),
                'phone' => $runningMate->getPhone() instanceof PhoneNumber ? $phoneUtils->format($runningMate->getPhone(), PhoneNumberFormat::INTERNATIONAL) : '',
                'favoriteCities' => $runningMate->getFavoriteCities(),
                'cvLink' => [
                    'label' => 'Télécharger le CV',
                    'url' => $this->urlGenerator->generate('asset_url', [
                        'path' => $runningMate->getPathWithDirectory(),
                        'mime_type' => 'application/pdf'
                    ]),
                ],
                'isAdherent' => $isAdherent ? 'Oui' : 'Non',
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
            $isAdherent = (bool) $volunteer['isAdherent'];
            $volunteer = $volunteer[0];
            $data[] = [
                'lastName' => $volunteer->getLastName(),
                'firstName' => $volunteer->getFirstName(),
                'phone' => $volunteer->getPhone(),
                'favoriteCities' => $volunteer->getFavoriteCities(),
                'isAdherent' => $isAdherent ? 'Oui' : 'Non',
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }

}
