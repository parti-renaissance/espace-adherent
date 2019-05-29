<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadApplicationRequestRunningMateRequestData extends Fixture
{
    private const EMAILS = ['bruce.banner@gmail.com', 'damien.schmidt@example.ch'];

    public function load(ObjectManager $manager)
    {
        foreach (self::EMAILS as $email) {
            $runningMateRequest = new RunningMateRequest();

            $phoneUtil = PhoneNumberUtil::getInstance();
            $runningMateRequest->setFirstName('Bruce');
            $runningMateRequest->setLastName('Banner');
            $runningMateRequest->setFavoriteCities(['New York']);
            $runningMateRequest->setEmailAddress($email);
            $runningMateRequest->setPostalCode('10001');
            $runningMateRequest->setCity('New York City');
            $runningMateRequest->setCountry('US');
            $runningMateRequest->setAddress('890 Fifth Avenue, Manhattan');
            $runningMateRequest->setPhone($phoneUtil->parse('06-06-06-06-06', "FR"));
            $runningMateRequest->setProfession('Scientist');

            $file = new UploadedFile(__DIR__.'/../running-mate-request/cv.pdf', 'cv.pdf', 'application/pdf');
            $runningMateRequest->setCurriculumNameFromUploadedFile($file);
            copy(__DIR__.'/../running-mate-request/cv.pdf', sprintf('%s/%s', __DIR__.'/../../../app/data', $runningMateRequest->getPathWithDirectory()));

            $runningMateRequest->addFavoriteTheme($this->getReference('application-theme-06'));
            $runningMateRequest->addFavoriteTheme($this->getReference('application-theme-08'));
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

            $manager->persist($runningMateRequest);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadApplicationRequestThemeData::class
        ];
    }
}
