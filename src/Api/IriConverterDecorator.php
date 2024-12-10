<?php

namespace App\Api;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use Ramsey\Uuid\Uuid;

class IriConverterDecorator implements IriConverterInterface
{
    public function __construct(private readonly IriConverterInterface $decorated)
    {
    }

    public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        if (Uuid::isValid($iri)) {
            $iri = $this->decorated->getIriFromResource(resource: $context['resource_class'], context: ['uri_variables' => ['uuid' => $iri]]);
        }

        return $this->decorated->getResourceFromIri($iri, $context, $operation);
    }

    public function getIriFromResource(
        object|string $resource,
        int $referenceType = UrlGeneratorInterface::ABS_PATH,
        ?Operation $operation = null,
        array $context = [],
    ): ?string {
        return $this->decorated->getIriFromResource($resource, $referenceType, $operation, $context);
    }
}
