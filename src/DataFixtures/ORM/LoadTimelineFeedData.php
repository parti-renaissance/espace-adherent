<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\JeMengage\Timeline\Mirror\TimelineFeedResolver;
use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class LoadTimelineFeedData extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TimelineFeedResolver $resolver,
        private readonly TimelineFeedWriter $writer,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->rootClasses() as $rootClass) {
            foreach ($this->entityManager->getRepository($rootClass)->findAll() as $entity) {
                $document = $this->resolver->resolve($entity);

                if (null !== $document && !$document->isRemoval()) {
                    $this->writer->upsert($document);
                }
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            LoadEventData::class,
            LoadCommitteeEventData::class,
            LoadActionData::class,
            LoadAdherentMessageData::class,
            LoadJecouteNewsData::class,
            LoadJecouteSurveyData::class,
            LoadJecouteRiposteData::class,
        ];
    }

    private function rootClasses(): array
    {
        $roots = [];
        foreach (array_keys(TimelineFeedTypeEnum::CLASS_MAPPING) as $class) {
            $root = $this->entityManager->getClassMetadata($class)->rootEntityName;
            $roots[$root] = $root;
        }

        return array_values($roots);
    }
}
