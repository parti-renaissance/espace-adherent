<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Content\MediaFactory;
use AppBundle\Content\ProposalFactory;
use AppBundle\Entity\ProposalTheme;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;

class LoadProposalData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get(ProposalFactory::class);
        $mediaFactory = $this->container->get(MediaFactory::class);
        $storage = $this->container->get('app.storage');
        $em = $this->container->get('doctrine.orm.entity_manager');

        // Media
        $mediaFile = new File(__DIR__.'/../../../data/dist/guadeloupe.jpg');
        $storage->put('images/proposal.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $mediaFactory->createFromFile('Proposition image', 'proposal.jpg', $mediaFile);
        $em->persist($media);

        $manager->flush();

        // Themes
        $manager->persist($education = new ProposalTheme('Education'));
        $manager->persist($environment = new ProposalTheme('Environnement'));
        $manager->persist($work = new ProposalTheme('Travail'));
        $manager->persist($solidarity = new ProposalTheme('Solidarité'));
        $manager->persist($agriculture = new ProposalTheme('Agriculture'));
        $manager->persist($socialProtection = new ProposalTheme('Protection sociale'));

        $manager->flush();

        // Proposals
        $manager->persist($factory->createFromArray([
            'position' => 1,
            'title' => 'Produire en France et sauver la planète',
            'slug' => 'produire-en-france-et-sauver-la-planete',
            'description' => 'Produire en France et sauver la planète',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'themes' => [$agriculture, $environment],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($factory->createFromArray([
            'position' => 2,
            'title' => 'Mieux vivre de son travail',
            'slug' => 'mieux-vivre-de-son-travail',
            'description' => 'Mieux vivre de son travail',
            'media' => $media,
            'displayMedia' => false,
            'published' => false,
            'themes' => [$work],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($factory->createFromArray([
            'position' => 3,
            'title' => 'Eduquer tous nos enfants',
            'slug' => 'eduquer-tous-nos-enfants',
            'description' => 'Eduquer tous nos enfants',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'themes' => [$education],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->flush();
    }
}
