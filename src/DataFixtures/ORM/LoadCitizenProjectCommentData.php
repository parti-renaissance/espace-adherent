<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectComment;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenProjectCommentData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $comment1 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-2'),
            'Jean-Paul à Maurice : tout va bien ! Je répète ! Tout va bien !'
        );
        $this->addReference('citizen-project-comment-1', $comment1);

        $comment2 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-4'),
            'Maurice à Jean-Paul : tout va bien aussi !'
        );
        $this->addReference('citizen-project-comment-2', $comment2);

        $comment3 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-3'),
            $this->getReference('adherent-7'),
            'Super commentaire d\'un adhérent très motivé pour se mettre En Marche !'
        );
        $this->addReference('citizen-project-comment-3', $comment3);

        $comment4 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-3'),
            $this->getReference('adherent-7'),
            'Un autre commentaire'
        );
        $this->addReference('citizen-project-comment-4', $comment4);

        $comment5 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-4'),
            $this->getReference('adherent-7'),
            'Le 5ème commentaire : ça commence à faire pas mal'
        );
        $this->addReference('citizen-project-comment-5', $comment5);

        $comment6 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-5'),
            $this->getReference('adherent-3'),
            'Le 6ème commentaire : la folie guette l\'univers tout entier'
        );
        $this->addReference('citizen-project-comment-6', $comment6);

        $comment7 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-5'),
            $this->getReference('adherent-9'),
            'L\'univers est perdu'
        );
        $this->addReference('citizen-project-comment-7', $comment7);

        $comment8 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-9'),
            $this->getReference('adherent-13'),
            'Contenu de test pour Michel 1'
        );
        $this->addReference('citizen-project-comment-8', $comment8);

        $comment9 = $this->createCitizenProjectComment(
            $this->getReference('citizen-project-9'),
            $this->getReference('adherent-13'),
            'Contenu de test pour Michel 2'
        );
        $this->addReference('citizen-project-comment-9', $comment9);

        $manager->persist($comment1);
        $manager->persist($comment2);
        $manager->persist($comment3);
        $manager->persist($comment4);
        $manager->persist($comment5);
        $manager->persist($comment6);
        $manager->persist($comment7);
        $manager->persist($comment8);
        $manager->persist($comment9);

        $manager->flush();
    }

    private function createCitizenProjectComment(CitizenProject $citizenProject, Adherent $author, string $content): CitizenProjectComment
    {
        return new CitizenProjectComment(null, $citizenProject, $author, $content);
    }

    public function getDependencies()
    {
        return [
            LoadCitizenProjectData::class,
        ];
    }
}
