<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadApplicationRequestVolunteerRequestData extends Fixture
{
    private const EMAILS = ['tony.stark@stark-industries.com', 'damien.schmidt@example.ch'];

    public function load(ObjectManager $manager)
    {
        foreach (self::EMAILS as $email) {
            $volunteerRequest = new VolunteerRequest();

            $volunteerRequest->setFirstName('Tony');
            $volunteerRequest->setLastName('Stark');
            $volunteerRequest->setFavoriteCities(['Malibu', 'New-York']);
            $volunteerRequest->setEmailAddress($email);
            $volunteerRequest->setPostalCode('90265');
            $volunteerRequest->setCity('Malibu');
            $volunteerRequest->setCountry('US');
            $volunteerRequest->setAddress('10880 Malibu Point');
            $volunteerRequest->setProfession('Scientist & Engineer');

            $volunteerRequest->addFavoriteTheme($this->getReference('application-theme-06'));
            $volunteerRequest->addFavoriteTheme($this->getReference('application-theme-08'));
            $volunteerRequest->setCustomFavoriteTheme('Thanos destruction');

            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-01'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-02'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-04'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-08'));

            $volunteerRequest->setCustomTechnicalSkills("I'm a man with a cool robot suit.");
            $volunteerRequest->setIsPreviousCampaignMember(false);
            $volunteerRequest->setShareAssociativeCommitment(false);

            $manager->persist($volunteerRequest);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadApplicationRequestThemeData::class,
            LoadApplicationRequestTechnicalSkillData::class,
        ];
    }
}
