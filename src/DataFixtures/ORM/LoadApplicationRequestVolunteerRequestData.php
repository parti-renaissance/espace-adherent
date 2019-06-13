<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumberUtil;

class LoadApplicationRequestVolunteerRequestData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $municipal1 = $this->getReference('municipal-chief-1');
        $municipal2 = $this->getReference('municipal-chief-2');
        $municipal3 = $this->getReference('municipal-chief-3');

        $volunteerRequest1 = new VolunteerRequest();
        $volunteerRequest1->setFavoriteCities([
            $municipal1->municipalChiefManagedArea()->getCodes()[0],
            $municipal2->municipalChiefManagedArea()->getCodes()[1],
        ]);
        $volunteerRequest1->setEmailAddress('tony.stark@stark-industries.com');

        $volunteerRequest2 = new VolunteerRequest();
        $volunteerRequest2->setFavoriteCities([
            $municipal2->municipalChiefManagedArea()->getCodes()[0],
            $municipal3->municipalChiefManagedArea()->getCodes()[1],
        ]);
        $volunteerRequest2->setEmailAddress('damien.schmidt@example.ch');

        $volunteerRequest3 = new VolunteerRequest();
        $volunteerRequest3->setFavoriteCities([
            $municipal1->municipalChiefManagedArea()->getCodes()[2],
            $municipal3->municipalChiefManagedArea()->getCodes()[0],
        ]);
        $volunteerRequest3->setEmailAddress('tony.stark@stark-industries.com');

        $volunteerRequest4 = new VolunteerRequest();
        $volunteerRequest4->setFavoriteCities([
            $municipal1->municipalChiefManagedArea()->getCodes()[2],
        ]);
        $volunteerRequest4->setEmailAddress('damien.schmidt@example.ch');

        $phone = PhoneNumberUtil::getInstance()->parse('06-06-06-06-06', 'FR');
        foreach ([$volunteerRequest1, $volunteerRequest2, $volunteerRequest3, $volunteerRequest4] as $i => $volunteerRequest) {
            $volunteerRequest->setFirstName('Tony');
            $volunteerRequest->setLastName('Stark');
            $volunteerRequest->setPostalCode('90265');
            $volunteerRequest->setCityName('Malibu');
            $volunteerRequest->setCountry('US');
            $volunteerRequest->setAddress('10880 Malibu Point');
            $volunteerRequest->setProfession('Scientist & Engineer');
            $volunteerRequest->setPhone($phone);

            $volunteerRequest->addFavoriteTheme($this->getReference('application-theme-06'));
            $volunteerRequest->addFavoriteTheme($this->getReference('application-theme-08'));
            $volunteerRequest->addTag($this->getReference('application-request-tag-'.rand(1, 3)));
            $volunteerRequest->addTag($this->getReference('application-request-tag-'.rand(2, 4)));
            $volunteerRequest->setCustomFavoriteTheme('Thanos destruction');

            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-01'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-02'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-04'));
            $volunteerRequest->addTechnicalSkill($this->getReference('application-skill-08'));

            $volunteerRequest->setCustomTechnicalSkills("I'am a man with a cool robot suit.");
            $volunteerRequest->setIsPreviousCampaignMember(false);
            $volunteerRequest->setShareAssociativeCommitment(false);

            $volunteerRequest->addReferentTag($this->getReference(sprintf('referent_tag_%s', (bool) ($i % 2) ? '75' : '62')));

            $manager->persist($volunteerRequest);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadApplicationRequestThemeData::class,
            LoadApplicationRequestTechnicalSkillData::class,
            LoadReferentTagData::class,
        ];
    }
}
