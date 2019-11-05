<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ProgrammaticFoundation\Tag;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadProgrammaticFoundationTagData extends AbstractFixture
{
    private const TAG_LABELS = [
        'Écologie',
        'Santé',
        'Économie',
        'Loisir',
        'Avenir',
        'Jeunesse',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::TAG_LABELS as $key => $label) {
            $tag = new Tag($label);
            $manager->persist($tag);
            $this->addReference("programmatic-foundation-tag-$key", $tag);
        }

        $manager->flush();
    }
}
