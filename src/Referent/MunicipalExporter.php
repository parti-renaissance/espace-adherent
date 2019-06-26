<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MunicipalExporter
{
    /**
     * @var Adherent
     */
    private $user;
    private $urlGenerator;
    private $phoneUtils;

    public function __construct(TokenStorageInterface $tokenStorage, UrlGeneratorInterface $urlGenerator)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->urlGenerator = $urlGenerator;
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
            $data[$i] = $this->exportApplicationRequestAsArray($i, $runningMate, $detailRoute, $tagsEditRoute);
            $data[$i]['cvLink'] = [
                'label' => 'Télécharger le CV',
                'url' => $this->urlGenerator->generate('asset_url', [
                    'path' => $runningMate->getPathWithDirectory(),
                    'mime_type' => 'application/pdf',
                ]),
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
            $data[] = $this->exportApplicationRequestAsArray($i, $volunteer, $detailRoute, $tagsEditRoute);
        }

        return \GuzzleHttp\json_encode($data);
    }

    private function exportApplicationRequestAsArray(
        int $i,
        ApplicationRequest $applicationRequest,
        string $detailRoute,
        string $tagsEditRoute
    ): array {
        $data = [
            'lastName' => $applicationRequest->getLastName(),
            'firstName' => $applicationRequest->getFirstName(),
            'phone' => $applicationRequest->getPhone() instanceof PhoneNumber
                ? $this->phoneUtils->format($applicationRequest->getPhone(), PhoneNumberFormat::INTERNATIONAL)
                : '',
            'favoriteCities' => implode(', ', $applicationRequest->getFavoriteCitiesNames()),
            'tags' => implode(', ', $applicationRequest->getTags()->toArray()),
            'isAdherent' => $applicationRequest->isAdherent() ? 'Oui' : 'Non',
            'show' => [
                'label' => "<span id='application-detail-$i' class='btn btn--default'><i class='fa fa-eye'></i></span>",
                'url' => $this->urlGenerator->generate($detailRoute, [
                    'uuid' => $applicationRequest->getUuid(),
                ]),
            ],
            'edit' => [
                'label' => "<span id='application-edit-$i' class='btn btn--default'><i class='fa fa-edit'></i></span>",
                'url' => $this->urlGenerator->generate($tagsEditRoute, [
                    'uuid' => $applicationRequest->getUuid(),
                ]),
            ],
        ];

        return $this->addTeamLink($i, $applicationRequest, $data);
    }

    private function addTeamLink(int $i, ApplicationRequest $applicationRequest, array $data): array
    {
        if (null !== $this->user && $this->user->isMunicipalChief()) {
            $managedAreasCodes = $this->user->getMunicipalChiefManagedArea()->getCodes();
            $prefix = $applicationRequest instanceof RunningMateRequest ? 'running_mate' : 'volunteer';

            if (null === $applicationRequest->getTakenForCity()) {
                $data['team'] = [
                    'label' => "<span id='application-team-$i' class='btn btn--default'><i class='fa fa-plus'></i></span>",
                    'url' => $this->urlGenerator->generate('app_municipal_chief_municipal_'.$prefix.'_request_add_to_team', [
                        'uuid' => $applicationRequest->getUuid(),
                    ]),
                ];
            } else {
                $canRemove = \in_array($applicationRequest->getTakenForCity(), $managedAreasCodes);
                $data['team'] = [
                    'label' => "<span id='application-team-$i' class='btn btn--default".(!$canRemove ? ' btn--disabled' : '')."'><i class='fa fa-".($canRemove ? 'minus' : 'plus')."'></i></span>",
                    'url' => $canRemove ? $this->urlGenerator->generate('app_municipal_chief_municipal_'.$prefix.'_request_remove_from_team', [
                        'uuid' => $applicationRequest->getUuid(),
                    ]) : '#',
                ];
            }
        }

        return $data;
    }
}
