<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\UserActionHistory;
use App\History\UserActionHistoryTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadUserActionHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1');
        /** @var Administrator $administrator1 */
        $administrator1 = $this->getReference('administrator-2');

        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::LOGIN_FAILURE, new \DateTime('-10 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PASSWORD_RESET_REQUEST, new \DateTime('-9 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PASSWORD_RESET_VALIDATE, new \DateTime('-8 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::LOGIN_SUCCESS, new \DateTime('-7 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PROFILE_UPDATE, new \DateTime('-6 minutes'), ['first_name']));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::IMPERSONATION_START, new \DateTime('-5 minutes'), null, $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::PROFILE_UPDATE, new \DateTime('-4 minutes'), ['birthdate'], $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::IMPERSONATION_END, new \DateTime('-3 minutes'), null, $administrator1));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::EMAIL_CHANGE_REQUEST, new \DateTime('-2 minutes')));
        $manager->persist($this->create($adherent1, UserActionHistoryTypeEnum::EMAIL_CHANGE_VALIDATE, new \DateTime('-1 minutes')));

        $manager->flush();
    }

    private function create(
        Adherent $adherent,
        UserActionHistoryTypeEnum $type,
        ?\DateTimeInterface $date = null,
        ?array $data = null,
        ?Administrator $impersonator = null,
    ): UserActionHistory {
        return new UserActionHistory(
            $adherent,
            $type,
            $date ?? new \DateTime('now'),
            $data,
            $impersonator
        );
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
        ];
    }
}
