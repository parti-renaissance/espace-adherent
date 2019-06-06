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
        $i = 0;
        while ($i < 4) {
            $email = self::EMAILS[$i % 2];
            $volunteerRequest = new VolunteerRequest();

            $volunteerRequest->setFirstName('Tony');
            $volunteerRequest->setLastName('Stark');
            $volunteerRequest->setFavoriteCities(['Malibu', 'New-York']);
            $volunteerRequest->setEmailAddress($email);
            $volunteerRequest->setPostalCode('90265');
            $volunteerRequest->setCityName('Malibu');
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

            $volunteerRequest->addReferentTag($this->getReference(sprintf('referent_tag_%s', (bool) ($i % 2) ? '75' : '62')));

            $manager->persist($volunteerRequest);
            ++$i;
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadApplicationRequestThemeData::class,
            LoadApplicationRequestTechnicalSkillData::class,
            LoadReferentTagData::class,
        ];
    }
}
