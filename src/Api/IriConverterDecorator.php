<?php

namespace App\Api;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Api\Exception\InvalidIdentifierException;
use App\Entity\Event\BaseEventCategory;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class IriConverterDecorator implements IriConverterInterface
{
    public function __construct(private readonly IriConverterInterface $decorated)
    {
    }

    public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        if (Uuid::isValid($iri)) {
            $iri = $this->decorated->getIriFromResource(resource: $context['resource_class'], context: ['uri_variables' => ['uuid' => $iri]]);
        } elseif (is_a($context['resource_class'], BaseEventCategory::class, true)) {
            $iri = $this->decorated->getIriFromResource(resource: $context['resource_class'], context: ['uri_variables' => ['slug' => $iri]]);
        }

        try {
            return $this->decorated->getResourceFromIri($iri, $context, $operation);
        } catch (InvalidArgumentException $e) {
            if ($e->getPrevious() instanceof ResourceNotFoundException) {
                throw new InvalidIdentifierException($e->getPrevious()->getMessage());
            }

            throw $e;
        }
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
