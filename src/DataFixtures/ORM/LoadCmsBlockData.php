<?php

namespace App\DataFixtures\ORM;

use App\AdherentCharter\AdherentCharterTypeEnum;
use App\Entity\CmsBlock;
use Doctrine\Persistence\ObjectManager;

class LoadCmsBlockData extends AbstractFixtures
{
    private const MARKDOWN_CONTENT = <<<MARKDOWN
# Lorem ipsum

Lorem ipsum dolor sit amet, consectetur adipiscing elit.
MARKDOWN;

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createCmsBlock(
            'phoning-campaign-tutorial',
            'Tutorial pour les campagnes de Phoning'
        ));

        $manager->persist($this->createCmsBlock(
            sprintf('chart-%s', AdherentCharterTypeEnum::TYPE_PHONING_CAMPAIGN),
            'Charte pour les appelants'
        ));

        $manager->flush();
    }

    private function createCmsBlock(
        string $name,
        string $description,
        string $content = self::MARKDOWN_CONTENT
    ): CmsBlock {
        return new CmsBlock($name, $description, $content);
    }
}
