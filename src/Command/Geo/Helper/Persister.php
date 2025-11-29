<?php

declare(strict_types=1);

namespace App\Command\Geo\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
final class Persister
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(SymfonyStyle $io, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->io = $io;
    }

    public function run(iterable $entities, bool $dryRun): void
    {
        $this->io->section('Persisting in database');

        if ($dryRun) {
            $this->io->comment('Nothing was persisted in database');

            return;
        }

        $this->em->transactional(static function (EntityManagerInterface $em) use ($entities) {
            foreach ($entities as $entity) {
                $em->persist($entity);
            }
        });

        $this->io->success('Done');
    }
}
