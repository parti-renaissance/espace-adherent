<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumberUtil;
use Ramsey\Uuid\Uuid;

class LoadApplicationRequestRunningMateRequestData extends Fixture
{
    private const UUID_1 = '23db4b50-dbe3-4b7f-9bd8-f3eaba8367de';
    private const UUID_2 = '64bae83d-0d29-42a7-a394-fb31648131f2';
    private const UUID_3 = 'b1f336d8-5a33-4e79-bf02-ae03d1101093';
    private const UUID_4 = 'ee608394-1ddd-4ae7-a4e7-cb9eb0f56348';

    public function load(ObjectManager $manager)
    {
        $municipal1 = $this->getReference('municipal-chief-1');
        $municipal2 = $this->getReference('municipal-chief-2');
        $municipal3 = $this->getReference('municipal-chief-3');

        $runningMateRequest1 = new RunningMateRequest(Uuid::fromString(self::UUID_1));
        $runningMateRequest1->setFavoriteCities([
            $municipal1->municipalChiefManagedArea()->getCodes()[0],
            $municipal2->municipalChiefManagedArea()->getCodes()[1],
        ]);
        $runningMateRequest1->setEmailAddress('bruce.banner@gmail.com');

        $runningMateRequest2 = new RunningMateRequest(Uuid::fromString(self::UUID_2));
        $runningMateRequest2->setFavoriteCities([
            $municipal1->municipalChiefManagedArea()->getCodes()[0],
            $municipal2->municipalChiefManagedArea()->getCodes()[0],
            $municipal3->municipalChiefManagedArea()->getCodes()[1],
        ]);
        $runningMateRequest2->setEmailAddress('damien.schmidt@example.ch');
        $runningMateRequest2->setAdherent($this->getReference('adherent-14'));

        $runningMateRequest3 = new RunningMateRequest(Uuid::fromString(self::UUID_3));
        $runningMateRequest3->setFavoriteCities([
            $municipal1->municipalChiefManagedArea()->getCodes()[2],
            $municipal3->municipalChiefManagedArea()->getCodes()[0],
        ]);
        $runningMateRequest3->setEmailAddress('bruce.banner@gmail.com');

        $runningMateRequest4 = new RunningMateRequest(Uuid::fromString(self::UUID_4));
        $runningMateRequest4->setFavoriteCities([
            $municipal1->municipalChiefManagedArea()->getCodes()[2],
        ]);
        $runningMateRequest4->setEmailAddress('damien.schmidt@example.ch');
        $runningMateRequest4->setAdherent($this->getReference('adherent-14'));

        $phone = PhoneNumberUtil::getInstance()->parse('06-06-06-06-06', 'FR');
        foreach ([$runningMateRequest1, $runningMateRequest2, $runningMateRequest3, $runningMateRequest4] as $i => $runningMateRequest) {
            $runningMateRequest->setFirstName('Bruce');
            $runningMateRequest->setLastName('Banner');
            $runningMateRequest->setPostalCode('10001');
            $runningMateRequest->setCityName('New York City');
            $runningMateRequest->setCountry('US');
            $runningMateRequest->setAddress('890 Fifth Avenue, Manhattan');
            $runningMateRequest->setPhone($phone);
            $runningMateRequest->setProfession('Scientist');

            $runningMateRequest->setCurriculumName('cv.pdf');

            $runningMateRequest->addFavoriteTheme($this->getReference('application-theme-06'));
            $runningMateRequest->addFavoriteTheme($this->getReference('application-theme-08'));
            $runningMateRequest->addTag($this->getReference('application-request-tag-'.$i));
            $runningMateRequest->addTag($this->getReference('application-request-tag-'.($i + 1) % 4));
            $runningMateRequest->setFavoriteThemeDetails('');
            $runningMateRequest->setCustomFavoriteTheme('Thanos destruction');

            $runningMateRequest->setIsLocalAssociationMember(false);
            $runningMateRequest->setIsPreviousElectedOfficial(false);
            $runningMateRequest->setPreviousElectedOfficialDetails('');
            $runningMateRequest->setLocalAssociationDomain('Fighting super villains');
            $runningMateRequest->setPoliticalActivistDetails('Putsch Thanos from his galactic throne');
            $runningMateRequest->setIsPoliticalActivist(false);
            $runningMateRequest->setProjectDetails('');
            $runningMateRequest->setProfessionalAssets('');

            $runningMateRequest->addReferentTag($this->getReference(sprintf('referent_tag_%s', (bool) ($i % 2) ? '75' : '62')));

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
