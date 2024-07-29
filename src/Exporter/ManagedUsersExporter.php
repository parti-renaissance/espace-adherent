<?php

namespace App\Exporter;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Projection\ManagedUser;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;

class ManagedUsersExporter
{
    private $exporter;
    private $repository;

    public function __construct(SonataExporter $exporter, ManagedUserRepository $repository)
    {
        $this->exporter = $exporter;
        $this->repository = $repository;
    }

    public function getResponse(string $format, ManagedUsersFilter $filter, ?string $spaceType = null): Response
    {
        switch ($spaceType) {
            case AdherentSpaceEnum::CANDIDATE:
                $callback = function (ManagedUser $managedUser) {
                    return [
                        'Prénom' => $managedUser->getFirstName(),
                        'Nom' => $managedUser->getLastName(),
                        'Âge' => $managedUser->getAge(),
                        'Genre' => $managedUser->getGenderLabel(),
                        'Adresse postale' => $managedUser->getAddress(),
                        'Commune' => $managedUser->getCity(),
                        'Code postal' => $managedUser->getPostalCode(),
                        'Pays' => $managedUser->getCountry(),
                    ];
                };

                break;
            case AdherentSpaceEnum::DEPUTY:
                $callback = function (ManagedUser $managedUser) {
                    return [
                        'Prénom' => $managedUser->getFirstName(),
                        'Nom' => $managedUser->getLastName(),
                        'Âge' => $managedUser->getAge(),
                        'Genre' => $managedUser->getGenderLabel(),
                        'Rôle' => $managedUser->getUserRoleLabels(),
                        'Adresse postale' => $managedUser->getAddress(),
                        'Commune' => $managedUser->getCity(),
                        'Code postal' => $managedUser->getPostalCode(),
                        'Pays' => $managedUser->getCountry(),
                        'Adhésion le' => $managedUser->getCreatedAt()->format('d/m/Y H:i'),
                        'Comités' => $managedUser->getCommitteesAsString(),
                    ];
                };

                break;
            default:
                $callback = function (ManagedUser $managedUser) {
                    return [
                        'Prénom' => $managedUser->getFirstName(),
                        'Nom' => $managedUser->getLastName(),
                        'Âge' => $managedUser->getAge(),
                        'Genre' => $managedUser->getGenderLabel(),
                        'Rôle' => $managedUser->getUserRoleLabels(),
                        'Commune' => $managedUser->getCity(),
                        'Code postal' => $managedUser->getPostalCode(),
                        'Pays' => $managedUser->getCountry(),
                        'Adhésion le' => $managedUser->getCreatedAt()->format('d/m/Y H:i'),
                        'Comité' => $managedUser->getCommittee(),
                    ];
                };
        }

        $array = new \ArrayObject($this->repository->getExportQueryBuilder($filter)->getResult());

        return $this->exporter->getResponse(
            $format,
            \sprintf('adherents--%s.%s', date('d-m-Y--H-i'), $format),
            new IteratorCallbackSourceIterator(
                $array->getIterator(),
                $callback
            )
        );
    }
}
