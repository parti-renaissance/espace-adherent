<?php

namespace App\Exporter;

use App\Entity\Projection\ManagedUser;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ManagedUsersExporter
{
    public function __construct(
        private readonly SonataExporter $exporter,
        private readonly ManagedUserRepository $repository,
        private readonly NormalizerInterface $normalizer
    ) {
    }

    public function getResponse(string $format, ManagedUsersFilter $filter): Response
    {
        $array = new \ArrayObject($this->repository->getExportQueryBuilder($filter)->getResult());

        return $this->exporter->getResponse(
            $format,
            sprintf('adherents--%s.%s', date('d-m-Y--H-i'), $format),
            new IteratorCallbackSourceIterator(
                $array->getIterator(),
                function (ManagedUser $managedUser): array {
                    return $this->transformToArray($managedUser);
                }
            )
        );
    }

    private function transformToArray(ManagedUser $managedUser): array
    {
        return $this->normalizer->normalize($managedUser, null, ['groups' => ['managed_user_read']]);
    }
}
