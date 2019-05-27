<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadApplicationRequestVolunteerRequestData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $volunteerRequest = new VolunteerRequest();

        $volunteerRequest->setFirstName('Tony');
        $volunteerRequest->setLastName('Stark');
        $volunteerRequest->setFavoriteCities(['Malibu', 'New-York']);
        $volunteerRequest->setEmailAddress('tony.stark@stark-industries.com');
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

        $volunteerRequest->setCustomTechnicalSkills("I'am a man with a super robot suit.");
        $volunteerRequest->setIsPreviousCampaignMember(false);
        $volunteerRequest->setShareAssociativeCommitment(false);

        $manager->persist($volunteerRequest);
        $manager->flush();
    }
}
