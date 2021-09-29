<?php

namespace App\DataFixtures\ORM;

use App\Entity\CmsBlock;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCmsBlockData extends Fixture
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
            'phoning-campaign-chart',
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
