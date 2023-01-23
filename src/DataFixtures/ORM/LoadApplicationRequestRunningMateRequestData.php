<?php

namespace App\DataFixtures\ORM;

use App\Address\Address;
use App\Entity\ApplicationRequest\RunningMateRequest;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use libphonenumber\PhoneNumberUtil;
use Ramsey\Uuid\Uuid;

class LoadApplicationRequestRunningMateRequestData extends Fixture implements DependentFixtureInterface
{
    private const UUID_1 = '23db4b50-dbe3-4b7f-9bd8-f3eaba8367de';
    private const UUID_2 = '64bae83d-0d29-42a7-a394-fb31648131f2';
    private const UUID_3 = 'b1f336d8-5a33-4e79-bf02-ae03d1101093';
    private const UUID_4 = 'ee608394-1ddd-4ae7-a4e7-cb9eb0f56348';

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $runningMateRequest1 = new RunningMateRequest(Uuid::fromString(self::UUID_1));
        $runningMateRequest1->setGender(Genders::FEMALE);
        $runningMateRequest1->setFirstName('Lorie');
        $runningMateRequest1->setLastName('Delisle');
        $runningMateRequest1->setEmailAddress('l.delisle@en-marche-dev.fr');
        $runningMateRequest1->setFavoriteCities(['59350', '59124']);

        $runningMateRequest2 = new RunningMateRequest(Uuid::fromString(self::UUID_2));
        $runningMateRequest2->setGender(Genders::MALE);
        $runningMateRequest2->setFirstName('Damien');
        $runningMateRequest2->setLastName('Schmidt');
        $runningMateRequest2->setEmailAddress('damien.schmidt@example.ch');
        $runningMateRequest2->setFavoriteCities(['59350', '59124', '59411']);

        $runningMateRequest2->setAdherent($this->getReference('adherent-14'));

        $runningMateRequest3 = new RunningMateRequest(Uuid::fromString(self::UUID_3));
        $runningMateRequest3->setGender(Genders::OTHER);
        $runningMateRequest3->setFirstName('Bruce');
        $runningMateRequest3->setLastName('Banner');
        $runningMateRequest3->setEmailAddress('bruce.banner@en-marche-dev.fr');
        $runningMateRequest3->setFavoriteCities(['59350', '59411']);

        $runningMateRequest4 = new RunningMateRequest(Uuid::fromString(self::UUID_4));
        $runningMateRequest4->setGender(Genders::MALE);
        $runningMateRequest4->setFirstName('Killian');
        $runningMateRequest4->setLastName('Jacquinot');
        $runningMateRequest4->setEmailAddress('kill.jac@example.ch');
        $runningMateRequest4->setFavoriteCities(['59411']);

        $phone = PhoneNumberUtil::getInstance()->parse('06-06-06-06-06', Address::FRANCE);

        foreach ([$runningMateRequest1, $runningMateRequest2, $runningMateRequest3, $runningMateRequest4] as $i => $runningMateRequest) {
            /** @var RunningMateRequest $runningMateRequest */
            $runningMateRequest->setPostalCode($faker->postcode());
            $runningMateRequest->setCityName($faker->city());
            $runningMateRequest->setCity('75012-75112');
            $runningMateRequest->setCountry(Address::FRANCE);
            $runningMateRequest->setAddress($faker->streetAddress());
            $runningMateRequest->setPhone($phone);
            $runningMateRequest->setProfession($faker->jobTitle());

            $runningMateRequest->setCurriculumName('cv.pdf');

            $runningMateRequest->addFavoriteTheme($this->getReference('application-theme-06'));
            $runningMateRequest->addFavoriteTheme($this->getReference('application-theme-08'));
            $runningMateRequest->addTag($this->getReference('application-request-tag-'.$i));
            $runningMateRequest->addTag($this->getReference('application-request-tag-'.($i + 1) % 4));
            $runningMateRequest->setFavoriteThemeDetails($faker->paragraph());
            $runningMateRequest->setCustomFavoriteTheme($faker->sentence());

            $runningMateRequest->setIsLocalAssociationMember(true);
            $runningMateRequest->setLocalAssociationDomain($faker->paragraph());
            $runningMateRequest->setIsPoliticalActivist(true);
            $runningMateRequest->setPoliticalActivistDetails($faker->paragraph());
            $runningMateRequest->setProjectDetails($faker->paragraph(10));
            $runningMateRequest->setProfessionalAssets($faker->paragraph(10));
            $runningMateRequest->setCreatedAt($faker->dateTimeBetween('-3 week'));
            $runningMateRequest->setUpdatedAt($faker->dateTimeBetween('-1 day'));
            $runningMateRequest->addReferentTag($this->getReference('referent_tag_59'));

            $manager->persist($runningMateRequest);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadReferentTagData::class,
            LoadApplicationRequestThemeData::class,
            LoadApplicationRequestTagData::class,
        ];
    }
}
