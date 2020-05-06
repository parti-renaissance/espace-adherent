<?php

namespace App\Exporter;

use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\DoctrineORMQuerySourceIterator;
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

    public function getResponse(string $format, ManagedUsersFilter $filter): Response
    {
        return $this->exporter->getResponse(
            $format,
            sprintf('adherents--%s.%s', date('d-m-Y--H-i'), $format),
            new DoctrineORMQuerySourceIterator(
                $this->repository->getExportQueryBuilder($filter),
                $this->getExportFields(),
                'd/m/Y H:i'
            )
        );
    }

    private function getExportFields(): array
    {
        return [
            'Prénom' => 'firstName',
            'Nom' => 'lastName',
            'Âge' => 'age',
            'Genre' => 'getGenderLabel',
            'Rôle' => 'getUserRoleLabels',
            'Commune' => 'city',
            'Code postal' => 'postalCode',
            'Pays' => 'country',
            'Adhésion le' => 'createdAt',
            'Comités' => 'getCommitteesAsString',
        ];
    }
}
