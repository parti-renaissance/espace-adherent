<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumberUtil;

class LoadApplicationRequestVolunteerRequestData extends Fixture
{
    private const EMAILS = ['tony.stark@stark-industries.com', 'damien.schmidt@example.ch'];

    public function load(ObjectManager $manager)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        $municipal1 = $this->getReference('municipal-chief-1');
        $municipal2 = $this->getReference('municipal-chief-2');
        $municipal3 = $this->getReference('municipal-chief-3');
        $i = 0;
        while ($i < 4) {
            $email = self::EMAILS[$i % 2];
            $volunteerRequest = new VolunteerRequest();
            switch ($i) {
                case 0:
                    $favoriteCities = [
                        $municipal1->municipalChiefManagedArea()->getCodes()[0],
                        $municipal2->municipalChiefManagedArea()->getCodes()[1],
                    ];
                    break;
                case 1:
                    $favoriteCities = [
                        $municipal2->municipalChiefManagedArea()->getCodes()[0],
                        $municipal3->municipalChiefManagedArea()->getCodes()[1],
                    ];
                    break;
                case 2:
                    $favoriteCities = [
                        $municipal1->municipalChiefManagedArea()->getCodes()[2],
                        $municipal3->municipalChiefManagedArea()->getCodes()[0],
                    ];
                    break;
                default:
                    $favoriteCities = [
                        $municipal1->municipalChiefManagedArea()->getCodes()[2],
                    ];
                    break;
            }

            $volunteerRequest->setFavoriteCities($favoriteCities);
            $volunteerRequest->setFirstName('Tony');
            $volunteerRequest->setLastName('Stark');
            $volunteerRequest->setEmailAddress($email);
            $volunteerRequest->setPostalCode('90265');
            $volunteerRequest->setCityName('Malibu');
            $volunteerRequest->setCountry('US');
            $volunteerRequest->setAddress('10880 Malibu Point');
            $volunteerRequest->setProfession('Scientist & Engineer');
            $volunteerRequest->setPhone($phoneUtil->parse('06-06-06-06-06', 'FR'));

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
            LoadAdherentData::class,
            LoadApplicationRequestThemeData::class,
            LoadApplicationRequestTechnicalSkillData::class,
            LoadReferentTagData::class,
        ];
    }
}
