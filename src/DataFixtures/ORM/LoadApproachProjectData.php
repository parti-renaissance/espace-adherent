<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ProgrammaticFoundation\Project;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadApproachProjectData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $cityPool = [
            'Lyon',
            'Paris',
            'St Jean des Vignes',
            'Lozanne',
            'St Rambert',
            'Toulouse',
        ];

        for ($i = 0; $i < 24; ++$i) {
            $project = new Project(
                $i + 1,
                sprintf('Projet lorem %d', $i + 1),
                '<p style="text-align:center"><iframe frameborder="0" height="360" src="https://www.youtube.com/embed/QTACugN67yc" width="640"></iframe></p>

<p>Maecenas finibus aliquam risus, eu lobortis magna convallis in. Cras et diam dignissim, auctor turpis tincidunt, rutrum urna. Aenean nec lacinia est. Donec ultrices ante eu interdum euismod. Ut tortor mi, ullamcorper sed elit at, fringilla molestie leo. In efficitur arcu dui, id posuere nulla ultricies quis. Sed fringilla lacus sed enim vestibulum, at ornare metus scelerisque. Phasellus id sagittis neque. In justo quam, <a href="#">placerat a pretium a</a>, mattis et nibh. Duis consequat ac metus aliquam fermentum.</p>

<p>Proin eget laoreet mi, vel fermentum nulla. Mauris sit amet tortor bibendum, maximus enim eu, mollis quam. Mauris id dui condimentum, faucibus tortor nec, fringilla quam. In fringilla vestibulum justo eu auctor. Pellentesque commodo mi maximus leo scelerisque cursus. Fusce lobortis enim dui, in congue ligula commodo eu. Donec a augue et elit mattis iaculis. Maecenas ultricies molestie sem et efficitur.</p>

<p>Pellentesque convallis tempus magna, et sollicitudin odio fermentum sit amet. Vestibulum fringilla vel dui id accumsan. Pellentesque elementum bibendum tortor eu dignissim. Vestibulum consectetur eleifend elementum. Maecenas feugiat risus eget enim tempor, et dignissim diam hendrerit. Pellentesque elementum consectetur est, eu egestas augue mattis non. Ut arcu nisl, ornare et elit vitae, auctor convallis est. Curabitur congue finibus convallis.</p>',
                $cityPool[$i % 6],
                0 === $i % 4
            );

            $project
                ->addTag($this->getReference(sprintf('programmatic-foundation-tag-%d', $i % 6)))
                ->addTag($this->getReference(sprintf('programmatic-foundation-tag-%d', (($i + 1) % 6))))
            ;

            $manager->persist($project);
            $this->addReference("sub-approach-measure-project-$i", $project);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadProgrammaticFoundationTagData::class,
        ];
    }
}
