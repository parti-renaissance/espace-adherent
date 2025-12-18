<?php

declare(strict_types=1);

namespace App\Adherent\Merge;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Psr\Log\LoggerInterface;

class DynamicRelationMerger
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function migrateRelations(Adherent $source, Adherent $target, ?callable $progressCallback = null): void
    {
        $allMetadata = $this->em->getMetadataFactory()->getAllMetadata();
        $total = \count($allMetadata);
        $current = 0;

        $errors = [];

        foreach ($allMetadata as $metadata) {
            ++$current;
            $className = $metadata->getName();

            if (Adherent::class === $className) {
                continue;
            }

            foreach ($metadata->getAssociationMappings() as $assocName => $mapping) {
                if (Adherent::class !== $mapping['targetEntity']) {
                    continue;
                }

                if (!$mapping['isOwningSide']) {
                    continue;
                }

                if (ClassMetadataInfo::MANY_TO_MANY === $mapping['type']) {
                    continue;
                }

                $dql = \sprintf(
                    'UPDATE %s e SET e.%s = :target WHERE e.%s = :source',
                    $className,
                    $assocName,
                    $assocName
                );

                try {
                    $this->em->createQuery($dql)
                        ->setParameter('target', $target)
                        ->setParameter('source', $source)
                        ->execute();

                    if ($progressCallback) {
                        $progressCallback(\sprintf('Migration de %s (relation: %s)', $this->getShortName($className), $assocName), (int) (($current / $total) * 80));
                    }

                    usleep(20_000);
                } catch (\Exception $e) {
                    $message = $e->getMessage();

                    if (!str_contains($message, 'Base table or view not found')) {
                        $errors[] = \sprintf(
                            '[AdherentMerge] Erreur lors de la migration des relations de %s (relation: %s) : %s',
                            $this->getShortName($className),
                            $assocName,
                            $e->getMessage()
                        );
                    }
                }
            }
        }

        if ($errors) {
            $this->logger->error('Errors occurred during Adherent merge #'.$source->getId().' into #'.$target->getId(), ['errors' => $errors]);
        }
    }

    private function getShortName(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);

        return end($parts);
    }
}
