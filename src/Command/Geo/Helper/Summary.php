<?php

declare(strict_types=1);

namespace App\Command\Geo\Helper;

use App\Entity\Geo\GeoInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
final class Summary
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    public function run(iterable $entities): void
    {
        $this->io->section('Summary');

        $byType = $this->gather($entities);

        $rows = [];
        foreach ($byType as $class => $sum) {
            $rows[] = [
                $class,
                \count($sum['new']),
                $sum['active'],
                $sum['total'] - $sum['active'],
                $sum['total'],
            ];
        }

        $this->io->table(
            ['Entity', 'New', 'Active', 'Inactive', 'Total'],
            $rows
        );

        if ($this->io->isVerbose()) {
            foreach ($byType as $class => $sum) {
                if (0 === \count($sum['new'])) {
                    continue;
                }

                $this->io->section(\sprintf('New %s entities', $class));

                $items = [];
                foreach ($sum['new'] as $entity) {
                    $items[] = \sprintf('%s', $entity->getName());
                }

                $this->io->listing($items);
            }
        }
    }

    /**
     * @param GeoInterface[] $entities
     */
    private function gather(iterable $entities): array
    {
        $byType = [];

        foreach ($entities as $entity) {
            $class = $entity::class;
            if (!isset($byType[$class])) {
                $byType[$class] = [
                    'total' => 0,
                    'active' => 0,
                    'new' => [],
                ];
            }

            ++$byType[$class]['total'];

            if (!$entity->getId()) {
                $byType[$class]['new'][] = $entity;
            }

            $byType[$class]['active'] += $entity instanceof GeoInterface
                ? $entity->isActive()
                : 1;
        }

        return $byType;
    }
}
