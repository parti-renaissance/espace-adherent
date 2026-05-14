<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\UuidEntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Uid\Uuid;

class UuidToObjectTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, string $className)
    {
        if (!is_a($className, UuidEntityInterface::class, true)) {
            throw new \RuntimeException(\sprintf('Invalid class name "%s", expected %s', $className, UuidEntityInterface::class));
        }

        $this->repository = $entityManager->getRepository($className);
    }

    public function transform($value): mixed
    {
        if ($value instanceof UuidEntityInterface) {
            return $value->getUuid()->toRfc4122();
        }

        return $value;
    }

    public function reverseTransform($value): mixed
    {
        if ($value && Uuid::isValid($value) && $object = $this->repository->findOneByUuid($value)) {
            return $object;
        }

        return null;
    }
}
