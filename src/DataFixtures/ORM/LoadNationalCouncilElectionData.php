<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Instance\NationalCouncil\CandidaciesGroup;
use App\Entity\Instance\NationalCouncil\Candidacy;
use App\Entity\Instance\NationalCouncil\Election;
use App\Image\ImageManager;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadNationalCouncilElectionData extends Fixture implements DependentFixtureInterface
{
    private const AVATARS = [
        Genders::MALE => [
            __DIR__.'/../../../app/data/dist/avatar_homme_01.jpg',
            __DIR__.'/../../../app/data/dist/avatar_homme_02.jpg',
        ],
        Genders::FEMALE => [
            __DIR__.'/../../../app/data/dist/avatar_femme_01.jpg',
            __DIR__.'/../../../app/data/dist/avatar_femme_02.jpg',
        ],
    ];

    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist($election = new Election($this->getReference('designation-11')));

        $candidacyGroup = new CandidaciesGroup();
        $candidacyGroup->setLabel('Liste A - Élection des membres du Burex');

        for ($i = 1; $i <= 9; ++$i) {
            if (0 === $i % 3) {
                $manager->persist($candidacyGroup = new CandidaciesGroup());
                $candidacyGroup->setLabel('Liste '.\chr(65 + $i).' - Élection des membres du Burex');
            }

            /** @var Adherent $adherent */
            $adherent = $this->getReference('adherent-'.($i + 40));
            $manager->persist($candidacy = new Candidacy($election, $adherent));

            $instanceQuality = current($adherent->getNationalCouncilQualities());
            $candidacy->setQuality($instanceQuality ? $instanceQuality->getInstanceQuality()->getCode() : null);
            $candidacy->setBiography('Voluptas ea rerum eligendi rerum ipsum optio iusto qui. Harum minima labore tempore odio doloribus sint nihil veniam. Sint voluptas et ea cum ipsa aut. Odio ut sequi at optio mollitia asperiores voluptas.');
            $candidacy->setFaithStatement('Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.');
            $candidacy->setIsPublicFaithStatement(true);
            $candidacy->confirm();

            $candidacy->setImage(new UploadedFile(
                 self::AVATARS[$adherent->getGender()][array_rand(self::AVATARS[$adherent->getGender()])],
                 'image.jpg',
                 'image/jpeg',
                 null,
                 true
            ));
            $this->imageManager->saveImage($candidacy);

            $candidacyGroup->addCandidacy($candidacy);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadDesignationData::class,
        ];
    }
}
