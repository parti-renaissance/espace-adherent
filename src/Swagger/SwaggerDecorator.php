<?php

namespace AppBundle\Swagger;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use ApiPlatform\Core\PathResolver\OperationPathResolverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface
{
    private const TITLE = 'LREM Swagger';

    private $decorated;
    private $apiPathPrefix;
    private $resourceMetadataFactory;
    private $operationPathResolver;
    private $paginationEnabled;

    public function __construct(
        NormalizerInterface $decorated,
        string $apiPathPrefix,
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        OperationPathResolverInterface $operationPathResolver,
        bool $paginationEnabled
    ) {
        $this->decorated = $decorated;
        $this->apiPathPrefix = $apiPathPrefix;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->operationPathResolver = $operationPathResolver;
        $this->paginationEnabled = $paginationEnabled;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        foreach ($object->getResourceNameCollection() as $resourceClass) {
            $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

            if ($resourceMetadata->getCollectionOperationAttribute('get', 'pagination_enabled', true, true)) {
                $context = $resourceMetadata->getCollectionOperationAttribute('get', 'normalization_context', null, true);

                if (isset($context['groups']) && ($groups = $context['groups']) && \is_array($groups)) {
                    $docs = $this->overridePaginatedResponseFormat($docs, $resourceMetadata, $groups[0]);
                }
            }
        }

        $docs['info']['title'] = self::TITLE;

        return $docs;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    private function overridePaginatedResponseFormat(array $docs, ResourceMetadata $resourceMetadata, string $group): array
    {
        $path = sprintf('%s%s', $this->apiPathPrefix, $this->getPath($resourceMetadata->getShortName(), ['get'], 'collection'));

        $definition = sprintf('%s-%s', $resourceMetadata->getShortName(), $group);

        $docs['paths'][$path]['get']['responses'][200] = PaginatorSwagger::getPaginatedResponseFor($definition);

        return $docs;
    }

    /**
     * Gets the path for an operation.
     *
     * If the path ends with the optional _format parameter, it is removed
     * as optional path parameters are not yet supported.
     *
     * @see https://github.com/OAI/OpenAPI-Specification/issues/93
     */
    private function getPath(string $resourceShortName, array $operation, string $operationType): string
    {
        $path = $this->operationPathResolver->resolveOperationPath($resourceShortName, $operation, $operationType);
        if ('.{_format}' === substr($path, -10)) {
            $path = substr($path, 0, -10);
        }

        return $path;
    }
}
