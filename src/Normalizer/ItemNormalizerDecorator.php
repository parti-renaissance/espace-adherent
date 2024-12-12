<?php

namespace App\Normalizer;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Exception\InvalidValueException;
use ApiPlatform\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Serializer\AbstractItemNormalizer;
use ApiPlatform\Serializer\ContextTrait;
use ApiPlatform\Serializer\InputOutputMetadataTrait;
use ApiPlatform\Symfony\Security\ResourceAccessCheckerInterface;
use ApiPlatform\Util\ClassInfoTrait;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class ItemNormalizerDecorator extends AbstractObjectNormalizer
{
    use ClassInfoTrait;
    use ContextTrait;
    use InputOutputMetadataTrait;
    use SerializerAwareTrait;

    protected $propertyNameCollectionFactory;
    protected $propertyMetadataFactory;
    protected $resourceMetadataFactory;
    protected $iriConverter;
    protected $resourceClassResolver;
    protected $resourceAccessChecker;
    protected $propertyAccessor;
    protected $itemDataProvider;
    protected $allowPlainIdentifiers;
    protected $dataTransformers = [];
    protected $localCache = [];

    public function __construct(
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
        $propertyMetadataFactory,
        $iriConverter,
        $resourceClassResolver,
        ?PropertyAccessorInterface $propertyAccessor = null,
        ?NameConverterInterface $nameConverter = null,
        ?ClassMetadataFactoryInterface $classMetadataFactory = null,
        ?ItemDataProviderInterface $itemDataProvider = null,
        bool $allowPlainIdentifiers = false,
        array $defaultContext = [],
        iterable $dataTransformers = [],
        $resourceMetadataFactory = null,
        ?ResourceAccessCheckerInterface $resourceAccessChecker = null,
    ) {
        if (!isset($defaultContext['circular_reference_handler'])) {
            $defaultContext['circular_reference_handler'] = function ($object) {
                return $this->iriConverter->getIriFromResource($object);
            };
        }

        if (!interface_exists(AdvancedNameConverterInterface::class) && method_exists($this, 'setCircularReferenceHandler')) {
            $this->setCircularReferenceHandler($defaultContext['circular_reference_handler']);
        }

        parent::__construct($classMetadataFactory, $nameConverter, null, null, \Closure::fromCallable([$this, 'getObjectClass']), $defaultContext);

        $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
        $this->propertyMetadataFactory = $propertyMetadataFactory;
        $this->iriConverter = $iriConverter;
        $this->resourceClassResolver = $resourceClassResolver;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
        $this->itemDataProvider = $itemDataProvider;
        $this->allowPlainIdentifiers = $allowPlainIdentifiers;
        $this->dataTransformers = $dataTransformers;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->resourceAccessChecker = $resourceAccessChecker;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (!\is_object($data) || is_iterable($data)) {
            return false;
        }

        $class = $this->getObjectClass($data);
        if (($context['output']['class'] ?? null) === $class) {
            return true;
        }

        return $this->resourceClassResolver->isResourceClass($class);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $resourceClass = $this->getObjectClass($object);
        if (!($isTransformed = isset($context[AbstractItemNormalizer::IS_TRANSFORMED_TO_SAME_CLASS])) && $outputClass = $this->getOutputClass($resourceClass, $context)) {
            if (!$this->serializer instanceof NormalizerInterface) {
                throw new LogicException('Cannot normalize the output because the injected serializer is not a normalizer');
            }

            // Data transformers are deprecated, this is removed from 3.0
            if ($dataTransformer = $this->getDataTransformer($object, $outputClass, $context)) {
                $transformed = $dataTransformer->transform($object, $outputClass, $context);

                if ($object === $transformed) {
                    $context[AbstractItemNormalizer::IS_TRANSFORMED_TO_SAME_CLASS] = true;
                } else {
                    $context['api_normalize'] = true;
                    $context['api_resource'] = $object;
                    unset($context['output'], $context['resource_class']);
                }

                return $this->serializer->normalize($transformed, $format, $context);
            }

            unset($context['output'], $context['operation_name']);
            if ($this->resourceMetadataFactory instanceof ResourceMetadataCollectionFactoryInterface && !isset($context['operation'])) {
                $context['operation'] = $this->resourceMetadataFactory->create($context['resource_class'])->getOperation();
            }
            $context['resource_class'] = $outputClass;
            $context['api_sub_level'] = true;
            $context[self::ALLOW_EXTRA_ATTRIBUTES] = false;

            return $this->serializer->normalize($object, $format, $context);
        }

        if ($isTransformed) {
            unset($context[AbstractItemNormalizer::IS_TRANSFORMED_TO_SAME_CLASS]);
        }

        $iri = null;
        if ($this->resourceClassResolver->isResourceClass($resourceClass)) {
            $context = $this->initContext($resourceClass, $context);
        }

        $context['iri'] = true;
        $context['api_normalize'] = true;

        $emptyResourceAsIri = $context['api_empty_resource_as_iri'] ?? false;
        unset($context['api_empty_resource_as_iri']);

        if (isset($context['resources'])) {
            $context['resources'][$iri] = $iri;
        }

        $data = parent::normalize($object, $format, $context);

        if ($emptyResourceAsIri && \is_array($data) && 0 === \count($data)) {
            return $iri;
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        if (!isset($context['resource_class'])) {
            return false;
        }

        if (($context['input']['class'] ?? null) === $type) {
            return true;
        }

        return $this->localCache[$type] ?? $this->localCache[$type] = $this->resourceClassResolver->isResourceClass($type);
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        // Avoid issues with proxies if we populated the object
        if (isset($data['id']) && !isset($context[self::OBJECT_TO_POPULATE])) {
            if (isset($context['api_allow_update']) && true !== $context['api_allow_update']) {
                throw new NotNormalizableValueException('Update is not allowed for this operation.');
            }

            if (isset($context['resource_class'])) {
                $this->updateObjectToPopulate($data, $context);
            }
        }

        $resourceClass = $class;

        if (null !== $inputClass = $this->getInputClass($resourceClass, $context)) {
            if (null !== $dataTransformer = $this->getDataTransformer($data, $resourceClass, $context)) {
                $dataTransformerContext = $context;

                unset($context['input']);
                unset($context['resource_class']);

                if (!$this->serializer instanceof DenormalizerInterface) {
                    throw new LogicException('Cannot denormalize the input because the injected serializer is not a denormalizer');
                }

                if ($dataTransformer instanceof DataTransformerInitializerInterface) {
                    $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $dataTransformer->initialize($inputClass, $context);
                    $context[AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE] = true;
                }

                try {
                    $denormalizedInput = $this->serializer->denormalize($data, $inputClass, $format, $context);
                } catch (NotNormalizableValueException $e) {
                    throw new UnexpectedValueException('The input data is misformatted.', $e->getCode(), $e);
                }

                if (!\is_object($denormalizedInput)) {
                    throw new UnexpectedValueException('Expected denormalized input to be an object.');
                }

                return $dataTransformer->transform($denormalizedInput, $resourceClass, $dataTransformerContext);
            }

            unset($context['input']);
            unset($context['operation']);
            unset($context['operation_name']);
            $context['resource_class'] = $inputClass;

            if (!$this->serializer instanceof DenormalizerInterface) {
                throw new LogicException('Cannot denormalize the input because the injected serializer is not a denormalizer');
            }

            try {
                return $this->serializer->denormalize($data, $inputClass, $format, $context);
            } catch (NotNormalizableValueException $e) {
                throw new UnexpectedValueException('The input data is misformatted.', $e->getCode(), $e);
            }
        }

        if (null === $objectToPopulate = $this->extractObjectToPopulate($class, $context, static::OBJECT_TO_POPULATE)) {
            $normalizedData = \is_scalar($data) ? [$data] : $this->prepareForDenormalization($data);
            $class = $this->getClassDiscriminatorResolvedClass($normalizedData, $class);
        }

        $context['api_denormalize'] = true;

        if ($this->resourceClassResolver->isResourceClass($class)) {
            $newResourceClass = $this->resourceClassResolver->getResourceClass($objectToPopulate, $class);
            if (!(new \ReflectionClass($newResourceClass))->isAbstract()) {
                $context['resource_class'] = $resourceClass = $newResourceClass;
            }
        }

        $supportsPlainIdentifiers = $this->supportsPlainIdentifiers();

        if (\is_string($data)) {
            try {
                return $this->iriConverter->getResourceFromIri($data, $context + ['fetch_data' => true]);
            } catch (ItemNotFoundException $e) {
                if (!$supportsPlainIdentifiers) {
                    throw new UnexpectedValueException($e->getMessage(), $e->getCode(), $e);
                }
            } catch (InvalidArgumentException $e) {
                if (!$supportsPlainIdentifiers) {
                    throw new UnexpectedValueException(\sprintf('Invalid IRI "%s".', $data), $e->getCode(), $e);
                }
            }
        }

        if (!\is_array($data)) {
            if (!$supportsPlainIdentifiers) {
                throw new UnexpectedValueException(\sprintf('Expected IRI or document for resource "%s", "%s" given.', $resourceClass, \gettype($data)));
            }

            $item = $this->itemDataProvider->getItem($resourceClass, $data, null, $context + ['fetch_data' => true]);
            if (null === $item) {
                throw new ItemNotFoundException(\sprintf('Item not found for resource "%s" with id "%s".', $resourceClass, $data));
            }

            return $item;
        }

        $previousObject = null !== $objectToPopulate ? clone $objectToPopulate : null;
        $object = parent::denormalize($data, $resourceClass, $format, $context);

        if (!$this->resourceClassResolver->isResourceClass($context['resource_class'])) {
            return $object;
        }

        // Revert attributes that aren't allowed to be changed after a post-denormalize check
        foreach (array_keys($data) as $attribute) {
            if (!$this->canAccessAttributePostDenormalize($object, $previousObject, $attribute, $context)) {
                if (null !== $previousObject) {
                    $this->setValue($object, $attribute, $this->propertyAccessor->getValue($previousObject, $attribute));
                } else {
                    $propertyMetadata = $this->propertyMetadataFactory->create($resourceClass, $attribute, $this->getFactoryOptions($context));
                    $this->setValue($object, $attribute, $propertyMetadata->getDefault());
                }
            }
        }

        return $object;
    }

    protected function getClassDiscriminatorResolvedClass(array &$data, string $class): string
    {
        if (null === $this->classDiscriminatorResolver || (null === $mapping = $this->classDiscriminatorResolver->getMappingForClass($class))) {
            return $class;
        }

        if (!isset($data[$mapping->getTypeProperty()])) {
            throw new RuntimeException(\sprintf('Type property "%s" not found for the abstract object "%s"', $mapping->getTypeProperty(), $class));
        }

        $type = $data[$mapping->getTypeProperty()];
        if (null === ($mappedClass = $mapping->getClassForType($type))) {
            throw new RuntimeException(\sprintf('The type "%s" has no mapped class for the abstract object "%s"', $type, $class));
        }

        return $mappedClass;
    }

    protected function extractAttributes($object, $format = null, array $context = [])
    {
        return [];
    }

    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = [])
    {
        if (!parent::isAllowedAttribute($classOrObject, $attribute, $format, $context)) {
            return false;
        }

        return $this->canAccessAttribute(\is_object($classOrObject) ? $classOrObject : null, $attribute, $context);
    }

    protected function canAccessAttribute($object, string $attribute, array $context = []): bool
    {
        if (!$this->resourceClassResolver->isResourceClass($context['resource_class'])) {
            return true;
        }

        $options = $this->getFactoryOptions($context);
        /** @var PropertyMetadata|ApiProperty */
        $propertyMetadata = $this->propertyMetadataFactory->create($context['resource_class'], $attribute, $options);
        $security = $propertyMetadata instanceof PropertyMetadata ? $propertyMetadata->getAttribute('security') : $propertyMetadata->getSecurity();
        if ($this->resourceAccessChecker && $security) {
            return $this->resourceAccessChecker->isGranted($context['resource_class'], $security, [
                'object' => $object,
            ]);
        }

        return true;
    }

    protected function canAccessAttributePostDenormalize(
        $object,
        $previousObject,
        string $attribute,
        array $context = [],
    ): bool {
        $options = $this->getFactoryOptions($context);
        /** @var PropertyMetadata|ApiProperty */
        $propertyMetadata = $this->propertyMetadataFactory->create($context['resource_class'], $attribute, $options);
        $security = $propertyMetadata instanceof PropertyMetadata ? $propertyMetadata->getAttribute('security_post_denormalize') : $propertyMetadata->getSecurityPostDenormalize();
        if ($this->resourceAccessChecker && $security) {
            return $this->resourceAccessChecker->isGranted($context['resource_class'], $security, [
                'object' => $object,
                'previous_object' => $previousObject,
            ]);
        }

        return true;
    }

    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = [])
    {
        $this->setValue($object, $attribute, $this->createAttributeValue($attribute, $value, $format, $context));
    }

    protected function validateType(string $attribute, Type $type, $value, ?string $format = null)
    {
        $builtinType = $type->getBuiltinType();
        if (Type::BUILTIN_TYPE_FLOAT === $builtinType && null !== $format && str_contains($format, 'json')) {
            $isValid = \is_float($value) || \is_int($value);
        } else {
            $isValid = \call_user_func('is_'.$builtinType, $value);
        }

        if (!$isValid) {
            throw new UnexpectedValueException(\sprintf('The type of the "%s" attribute must be "%s", "%s" given.', $attribute, $builtinType, \gettype($value)));
        }
    }

    protected function denormalizeCollection(
        string $attribute,
        $propertyMetadata,
        Type $type,
        string $className,
        $value,
        ?string $format,
        array $context,
    ): array {
        if (!\is_array($value)) {
            throw new InvalidArgumentException(\sprintf('The type of the "%s" attribute must be "array", "%s" given.', $attribute, \gettype($value)));
        }

        $collectionKeyType = method_exists(Type::class, 'getCollectionKeyTypes') ? ($type->getCollectionKeyTypes()[0] ?? null) : $type->getCollectionKeyType();
        $collectionKeyBuiltinType = null === $collectionKeyType ? null : $collectionKeyType->getBuiltinType();

        $values = [];
        foreach ($value as $index => $obj) {
            if (null !== $collectionKeyBuiltinType && !\call_user_func('is_'.$collectionKeyBuiltinType, $index)) {
                throw new InvalidArgumentException(\sprintf('The type of the key "%s" must be "%s", "%s" given.', $index, $collectionKeyBuiltinType, \gettype($index)));
            }

            $values[$index] = $this->denormalizeRelation($attribute, $propertyMetadata, $className, $obj, $format, $this->createChildContext($context, $attribute, $format));
        }

        return $values;
    }

    protected function denormalizeRelation(
        string $attributeName,
        $propertyMetadata,
        string $className,
        $value,
        ?string $format,
        array $context,
    ) {
        $supportsPlainIdentifiers = $this->supportsPlainIdentifiers();

        if (\is_string($value)) {
            try {
                return $this->iriConverter->getResourceFromIri($value, $context + ['fetch_data' => true]);
            } catch (ItemNotFoundException $e) {
                if (!$supportsPlainIdentifiers) {
                    throw new UnexpectedValueException($e->getMessage(), $e->getCode(), $e);
                }
            } catch (InvalidArgumentException $e) {
                if (!$supportsPlainIdentifiers) {
                    throw new UnexpectedValueException(\sprintf('Invalid IRI "%s".', $value), $e->getCode(), $e);
                }
            }
        }

        if ($propertyMetadata->isWritableLink()) {
            $context['api_allow_update'] = true;

            if (!$this->serializer instanceof DenormalizerInterface) {
                throw new LogicException(\sprintf('The injected serializer must be an instance of "%s".', DenormalizerInterface::class));
            }

            try {
                $item = $this->serializer->denormalize($value, $className, $format, $context);
                if (!\is_object($item) && null !== $item) {
                    throw new \UnexpectedValueException('Expected item to be an object or null.');
                }

                return $item;
            } catch (InvalidValueException $e) {
                if (!$supportsPlainIdentifiers) {
                    throw $e;
                }
            }
        }

        if (!\is_array($value)) {
            if (!$supportsPlainIdentifiers) {
                throw new UnexpectedValueException(\sprintf('Expected IRI or nested document for attribute "%s", "%s" given.', $attributeName, \gettype($value)));
            }

            $item = $this->itemDataProvider->getItem($className, $value, null, $context + ['fetch_data' => true]);
            if (null === $item) {
                throw new ItemNotFoundException(\sprintf('Item not found for resource "%s" with id "%s".', $className, $value));
            }

            return $item;
        }

        throw new UnexpectedValueException(\sprintf('Nested documents for attribute "%s" are not allowed. Use IRIs instead.', $attributeName));
    }

    protected function getFactoryOptions(array $context): array
    {
        $options = [];

        if (isset($context[self::GROUPS])) {
            /* @see https://github.com/symfony/symfony/blob/v4.2.6/src/Symfony/Component/PropertyInfo/Extractor/SerializerExtractor.php */
            $options['serializer_groups'] = (array) $context[self::GROUPS];
        }

        if (isset($context['resource_class']) && $this->resourceClassResolver->isResourceClass($context['resource_class']) && $this->resourceMetadataFactory instanceof ResourceMetadataCollectionFactoryInterface) {
            $resourceClass = $this->resourceClassResolver->getResourceClass(null, $context['resource_class']); // fix for abstract classes and interfaces
            // This is a hot spot, we should avoid calling this here but in many cases we can't
            $operation = $context['operation'] ?? $this->resourceMetadataFactory->create($resourceClass)->getOperation($context['operation_name'] ?? null);
            $options['normalization_groups'] = $operation->getNormalizationContext()['groups'] ?? null;
            $options['denormalization_groups'] = $operation->getDenormalizationContext()['groups'] ?? null;
        }

        if (isset($context['operation_name'])) {
            $options['operation_name'] = $context['operation_name'];
        }

        if (isset($context['collection_operation_name'])) {
            $options['collection_operation_name'] = $context['collection_operation_name'];
        }

        if (isset($context['item_operation_name'])) {
            $options['item_operation_name'] = $context['item_operation_name'];
        }

        return $options;
    }

    protected function getAttributeValue($object, $attribute, $format = null, array $context = [])
    {
        $context['api_attribute'] = $attribute;
        /** @var ApiProperty|PropertyMetadata */
        $propertyMetadata = $this->propertyMetadataFactory->create($context['resource_class'], $attribute, $this->getFactoryOptions($context));

        try {
            $attributeValue = $this->propertyAccessor->getValue($object, $attribute);
        } catch (NoSuchPropertyException $e) {
            // BC to be removed in 3.0
            if ($propertyMetadata instanceof PropertyMetadata && !$propertyMetadata->hasChildInherited()) {
                throw $e;
            }

            if ($propertyMetadata instanceof ApiProperty) {
                throw $e;
            }

            $attributeValue = null;
        }

        if ($context['api_denormalize'] ?? false) {
            return $attributeValue;
        }

        $type = $propertyMetadata instanceof PropertyMetadata ? $propertyMetadata->getType() : ($propertyMetadata->getBuiltinTypes()[0] ?? null);

        if (
            $type
            && $type->isCollection()
            && ($collectionValueType = method_exists(Type::class, 'getCollectionValueTypes') ? ($type->getCollectionValueTypes()[0] ?? null) : $type->getCollectionValueType())
            && ($className = $collectionValueType->getClassName())
            && $this->resourceClassResolver->isResourceClass($className)
        ) {
            if (!is_iterable($attributeValue)) {
                throw new UnexpectedValueException('Unexpected non-iterable value for to-many relation.');
            }

            $resourceClass = $this->resourceClassResolver->getResourceClass($attributeValue, $className);
            $childContext = $this->createChildContext($context, $attribute, $format);
            $childContext['resource_class'] = $resourceClass;
            if ($this->resourceMetadataFactory instanceof ResourceMetadataCollectionFactoryInterface) {
                $childContext['operation'] = $this->resourceMetadataFactory->create($resourceClass)->getOperation();
            }
            unset($childContext['iri'], $childContext['uri_variables']);

            return $this->normalizeCollectionOfRelations($propertyMetadata, $attributeValue, $resourceClass, $format, $childContext);
        }

        if (
            $type
            && ($className = $type->getClassName())
            && $this->resourceClassResolver->isResourceClass($className)
        ) {
            if (!\is_object($attributeValue) && null !== $attributeValue) {
                throw new UnexpectedValueException('Unexpected non-object value for to-one relation.');
            }

            $resourceClass = $this->resourceClassResolver->getResourceClass($attributeValue, $className);
            $childContext = $this->createChildContext($context, $attribute, $format);
            $childContext['resource_class'] = $resourceClass;
            if ($this->resourceMetadataFactory instanceof ResourceMetadataCollectionFactoryInterface) {
                $childContext['operation'] = $this->resourceMetadataFactory->create($resourceClass)->getOperation();
            }
            unset($childContext['iri'], $childContext['uri_variables']);

            return $this->normalizeRelation($propertyMetadata, $attributeValue, $resourceClass, $format, $childContext);
        }

        if (!$this->serializer instanceof NormalizerInterface) {
            throw new LogicException(\sprintf('The injected serializer must be an instance of "%s".', NormalizerInterface::class));
        }

        unset($context['resource_class']);

        if ($type && $type->getClassName()) {
            $childContext = $this->createChildContext($context, $attribute, $format);
            unset($childContext['iri'], $childContext['uri_variables']);

            if ($propertyMetadata instanceof PropertyMetadata) {
                $childContext['output']['iri'] = $propertyMetadata->getIri() ?? false;
            } else {
                $childContext['output']['gen_id'] = $propertyMetadata->getGenId() ?? false;
            }

            return $this->serializer->normalize($attributeValue, $format, $childContext);
        }

        return $this->serializer->normalize($attributeValue, $format, $context);
    }

    protected function normalizeCollectionOfRelations(
        $propertyMetadata,
        $attributeValue,
        string $resourceClass,
        ?string $format,
        array $context,
    ): array {
        $value = [];
        foreach ($attributeValue as $index => $obj) {
            if (!\is_object($obj) && null !== $obj) {
                throw new UnexpectedValueException('Unexpected non-object element in to-many relation.');
            }

            $value[$index] = $this->normalizeRelation($propertyMetadata, $obj, $resourceClass, $format, $context);
        }

        return $value;
    }

    protected function normalizeRelation(
        $propertyMetadata,
        $relatedObject,
        string $resourceClass,
        ?string $format,
        array $context,
    ) {
        if (null === $relatedObject || !empty($context['attributes']) || $propertyMetadata->isReadableLink()) {
            if (!$this->serializer instanceof NormalizerInterface) {
                throw new LogicException(\sprintf('The injected serializer must be an instance of "%s".', NormalizerInterface::class));
            }

            $normalizedRelatedObject = $this->serializer->normalize($relatedObject, $format, $context);
            if (!\is_string($normalizedRelatedObject) && !\is_array($normalizedRelatedObject) && !$normalizedRelatedObject instanceof \ArrayObject && null !== $normalizedRelatedObject) {
                throw new UnexpectedValueException('Expected normalized relation to be an IRI, array, \ArrayObject or null');
            }

            return $normalizedRelatedObject;
        }

        $iri = $this->iriConverter->getIriFromResource($relatedObject);

        if (isset($context['resources'])) {
            $context['resources'][$iri] = $iri;
        }

        $push = $propertyMetadata instanceof PropertyMetadata ? $propertyMetadata->getAttribute('push', false) : ($propertyMetadata->getPush() ?? false);
        if (isset($context['resources_to_push']) && $push) {
            $context['resources_to_push'][$iri] = $iri;
        }

        return $iri;
    }

    protected function getDataTransformer($data, string $to, array $context = []): ?DataTransformerInterface
    {
        foreach ($this->dataTransformers as $dataTransformer) {
            if ($dataTransformer->supportsTransformation($data, $to, $context)) {
                return $dataTransformer;
            }
        }

        return null;
    }

    private function createAttributeValue($attribute, $value, $format = null, array $context = [])
    {
        if (!$this->resourceClassResolver->isResourceClass($context['resource_class'])) {
            return $value;
        }

        /** @var ApiProperty|PropertyMetadata */
        $propertyMetadata = $this->propertyMetadataFactory->create($context['resource_class'], $attribute, $this->getFactoryOptions($context));
        $type = $propertyMetadata instanceof PropertyMetadata ? $propertyMetadata->getType() : ($propertyMetadata->getBuiltinTypes()[0] ?? null);

        if (null === $type) {
            // No type provided, blindly return the value
            return $value;
        }

        if (null === $value && $type->isNullable()) {
            return $value;
        }

        $collectionValueType = method_exists(Type::class, 'getCollectionValueTypes') ? ($type->getCollectionValueTypes()[0] ?? null) : $type->getCollectionValueType();

        /* From @see AbstractObjectNormalizer::validateAndDenormalize() */
        // Fix a collection that contains the only one element
        // This is special to xml format only
        if ('xml' === $format && null !== $collectionValueType && (!\is_array($value) || !\is_int(key($value)))) {
            $value = [$value];
        }

        if (
            $type->isCollection()
            && null !== $collectionValueType
            && null !== ($className = $collectionValueType->getClassName())
            && $this->resourceClassResolver->isResourceClass($className)
        ) {
            $resourceClass = $this->resourceClassResolver->getResourceClass(null, $className);
            $context['resource_class'] = $resourceClass;

            return $this->denormalizeCollection($attribute, $propertyMetadata, $type, $resourceClass, $value, $format, $context);
        }

        if (
            null !== ($className = $type->getClassName())
            && $this->resourceClassResolver->isResourceClass($className)
        ) {
            $resourceClass = $this->resourceClassResolver->getResourceClass(null, $className);
            $childContext = $this->createChildContext($context, $attribute, $format);
            $childContext['resource_class'] = $resourceClass;
            if ($this->resourceMetadataFactory instanceof ResourceMetadataCollectionFactoryInterface) {
                $childContext['operation'] = $this->resourceMetadataFactory->create($resourceClass)->getOperation();
            }

            return $this->denormalizeRelation($attribute, $propertyMetadata, $resourceClass, $value, $format, $childContext);
        }

        if (
            $type->isCollection()
            && null !== $collectionValueType
            && null !== ($className = $collectionValueType->getClassName())
        ) {
            if (!$this->serializer instanceof DenormalizerInterface) {
                throw new LogicException(\sprintf('The injected serializer must be an instance of "%s".', DenormalizerInterface::class));
            }

            unset($context['resource_class']);

            return $this->serializer->denormalize($value, $className.'[]', $format, $context);
        }

        if (null !== $className = $type->getClassName()) {
            if (!$this->serializer instanceof DenormalizerInterface) {
                throw new LogicException(\sprintf('The injected serializer must be an instance of "%s".', DenormalizerInterface::class));
            }

            unset($context['resource_class']);

            return $this->serializer->denormalize($value, $className, $format, $context);
        }

        /* From @see AbstractObjectNormalizer::validateAndDenormalize() */
        // In XML and CSV all basic datatypes are represented as strings, it is e.g. not possible to determine,
        // if a value is meant to be a string, float, int or a boolean value from the serialized representation.
        // That's why we have to transform the values, if one of these non-string basic datatypes is expected.
        if (\is_string($value) && (XmlEncoder::FORMAT === $format || CsvEncoder::FORMAT === $format)) {
            if ('' === $value && $type->isNullable() && \in_array($type->getBuiltinType(), [Type::BUILTIN_TYPE_BOOL, Type::BUILTIN_TYPE_INT, Type::BUILTIN_TYPE_FLOAT], true)) {
                return null;
            }

            switch ($type->getBuiltinType()) {
                case Type::BUILTIN_TYPE_BOOL:
                    // according to https://www.w3.org/TR/xmlschema-2/#boolean, valid representations are "false", "true", "0" and "1"
                    if ('false' === $value || '0' === $value) {
                        $value = false;
                    } elseif ('true' === $value || '1' === $value) {
                        $value = true;
                    } else {
                        throw new NotNormalizableValueException(\sprintf('The type of the "%s" attribute for class "%s" must be bool ("%s" given).', $attribute, $className, $value));
                    }
                    break;
                case Type::BUILTIN_TYPE_INT:
                    if (ctype_digit($value) || ('-' === $value[0] && ctype_digit(substr($value, 1)))) {
                        $value = (int) $value;
                    } else {
                        throw new NotNormalizableValueException(\sprintf('The type of the "%s" attribute for class "%s" must be int ("%s" given).', $attribute, $className, $value));
                    }
                    break;
                case Type::BUILTIN_TYPE_FLOAT:
                    if (is_numeric($value)) {
                        return (float) $value;
                    }

                    switch ($value) {
                        case 'NaN':
                            return \NAN;
                        case 'INF':
                            return \INF;
                        case '-INF':
                            return -\INF;
                        default:
                            throw new NotNormalizableValueException(\sprintf('The type of the "%s" attribute for class "%s" must be float ("%s" given).', $attribute, $className, $value));
                    }
            }
        }

        if ($context[static::DISABLE_TYPE_ENFORCEMENT] ?? false) {
            return $value;
        }

        $this->validateType($attribute, $type, $value, $format);

        return $value;
    }

    private function setValue($object, string $attributeName, $value)
    {
        try {
            $this->propertyAccessor->setValue($object, $attributeName, $value);
        } catch (NoSuchPropertyException $exception) {
        }
    }

    private function updateObjectToPopulate(array $data, array &$context): void
    {
        try {
            $context[self::OBJECT_TO_POPULATE] = $this->iriConverter->getResourceFromIri((string) $data['id'], $context + ['fetch_data' => true]);
        } catch (InvalidArgumentException $e) {
            $iri = $this->iriConverter->getIriFromResource($context['resource_class'], UrlGeneratorInterface::ABS_PATH, null, ['uri_variables' => ['id' => $data['id']]]);
            $context[self::OBJECT_TO_POPULATE] = $this->iriConverter->getResourceFromIri($iri, ['fetch_data' => true]);
        }
    }

    private function supportsPlainIdentifiers(): bool
    {
        return $this->allowPlainIdentifiers && null !== $this->itemDataProvider;
    }
}
