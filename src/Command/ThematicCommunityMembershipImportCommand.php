<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\ThematicCommunity\AdherentMembership;
use App\Entity\ThematicCommunity\Contact;
use App\Entity\ThematicCommunity\ContactMembership;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use App\Membership\ActivityPositions;
use App\Repository\AdherentRepository;
use App\Repository\ThematicCommunity\ThematicCommunityRepository;
use App\Repository\UserListDefinitionRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThematicCommunityMembershipImportCommand extends Command
{
    private const CT_NAME_TO_TYPE = [
        'Ecole' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_EDUCATION,
        'Agriculture et alimentation' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_AGRICULTURE,
        'Ecologie' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_ECOLOGY,
        'Europe' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_EUROPE,
        'PME' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_TPE_PME,
        'Santé' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_HEALTH,
    ];

    private const BATCH_SIZE = 200;

    /** @var AdherentRepository */
    private $adherentRepository;

    /** @var ThematicCommunityRepository */
    private $communityRepository;

    /** @var array */
    private $userListDefinitions;

    /** @var UserListDefinitionRepository */
    private $userListDefinitionRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->adherentRepository = $entityManager->getRepository(Adherent::class);
        $this->communityRepository = $entityManager->getRepository(ThematicCommunity::class);
        $this->userListDefinitionRepository = $entityManager->getRepository(UserListDefinition::class);
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:thematic-community:import-membership')
            ->addArgument('file', InputArgument::REQUIRED, 'File to import')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $userListDefinitions = $this->userListDefinitionRepository->createQueryBuilder('uld')
            ->where('uld.type IN (:ct_types)')
            ->setParameter('ct_types', UserListDefinitionEnum::THEMATIC_COMMUNITY_CODES)
            ->getQuery()
            ->getResult()
        ;

        /** @var UserListDefinition $uld */
        foreach ($userListDefinitions as $uld) {
            $this->userListDefinitions[$uld->getType()][] = $uld;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $i = 0;
        $filename = $input->getArgument('file');

        $handle = \fopen($filename, 'rb');
        \fgetcsv($handle, 0, ';');
        while ($row = \fgetcsv($handle, 800, ';')) {
            $community = $this->communityRepository->findOneBy(['name' => $row[2]]);

            if (!$community) {
                $output->writeln("<info>Community $row[2] not found. Skipping...</info>");
                continue;
            }

            $status = ThematicCommunityMembership::STATUS_PENDING;

            if ('oui' === $row[23]) {
                $adherent = $this->adherentRepository->findOneByEmail($row[3]);

                if (!$adherent) {
                    continue;
                }

                $membership = new AdherentMembership();
                $membership->setAdherent($adherent);

                if ($adherent->isEnabled()) {
                    $status = ThematicCommunityMembership::STATUS_VERIFIED;
                }
            } else {
                $contact = new Contact();
                $this->setGender($contact, $row[7]);
                $contact->setFirstName($row[5]);
                $contact->setLastName($row[6]);
                $contact->setEmail($row[3]);
                $this->setPosition($contact, $row[9]);

                $membership = new ContactMembership();
                $membership->setContact($contact);
            }

            $membership->setCommunity($community);
            $membership->setJoinedAt(\DateTime::createFromFormat('d/m/Y H:i', $row[1]));
            $hasJob = 'oui' === \mb_strtolower($row[12]);
            $membership->setHasJob($hasJob);
            if ($hasJob) {
                $membership->setJob($row[13]);
            }
            $hasAssociation = 'Oui' === \mb_strtolower($row[14]);
            $membership->setAssociation($hasAssociation);
            if ($hasAssociation) {
                $membership->setAssociationName($row[15]);
            }
            $this->setMotivations($membership, $row[16], $row[17], $row[18]);
            $membership->setStatus($status);

            $this->setUserListDefinitions($membership, \array_filter(\array_map('trim', explode(',', $row[19]))));

            $this->entityManager->persist($membership);

            if (0 === ++$i % self::BATCH_SIZE) {
                $this->entityManager->flush();
                $this->entityManager->clear(ThematicCommunityMembership::class);
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        \fclose($handle);
    }

    protected function setUserListDefinitions(ThematicCommunityMembership $membership, array $categories): void
    {
        foreach ($categories as $category) {
            $type = self::CT_NAME_TO_TYPE[$membership->getCommunity()->getName()];
            $added = false;
            /** @var UserListDefinition $uld */
            foreach ($this->userListDefinitions[$type] ?? [] as $uld) {
                if ($uld->getLabel() === $category) {
                    $membership->addUserListDefinition($uld);
                    $added = true;
                    break;
                }
            }

            if (!$added) {
                $userListDefinition = new UserListDefinition($type, Urlizer::urlize($category), $category);
                $membership->addUserListDefinition($userListDefinition);
                $this->userListDefinitions[$type][] = $userListDefinition;
            }
        }
    }

    protected function setMotivations(
        ThematicCommunityMembership $membership,
        string $information,
        string $thinking,
        string $onSpot
    ): void {
        $motivations = [
            $information ? ThematicCommunityMembership::MOTIVATION_INFORMATION : null,
            $thinking ? ThematicCommunityMembership::MOTIVATION_THINKING : null,
            $onSpot ? ThematicCommunityMembership::MOTIVATION_ON_SPOT : null,
        ];

        $membership->setMotivations(\array_filter($motivations));
    }

    protected function setPosition(Contact $contact, string $position): void
    {
        switch ($position) {
            case 'Cadre':
                $contact->setPosition(ActivityPositions::EXECUTIVE);
                break;
            case 'Employé':
            case 'Employé(e)':
                $contact->setPosition(ActivityPositions::EMPLOYED);
                break;
            case 'Étudiant':
            case 'Étudiant(e)':
                $contact->setPosition(ActivityPositions::STUDENT);
                break;
            case 'Indépendants et professions libérales':
                $contact->setPosition(ActivityPositions::SELF_EMPLOYED_AND_LIBERAL_PROFESSIONS);
                break;
            case 'Je ne souhaite pas répondre':
                break;
            case 'Ouvrier':
                $contact->setPosition(ActivityPositions::WORKER);
                break;
            case 'Profession intermédiaire':
                $contact->setPosition(ActivityPositions::INTERMEDIATE_PROFESSION);
                break;
            case 'Retraité(e)':
            case 'Retraité':
                $contact->setPosition(ActivityPositions::RETIRED);
                break;
        }
    }

    protected function setGender(Contact $contact, string $gender): void
    {
        switch ($gender) {
            case 'Une femme':
                $contact->setGender(Genders::FEMALE);
                break;
            case 'Un homme':
                $contact->setGender(Genders::MALE);
                break;
            case 'Je ne souhaite pas répondre':
                break;
            default:
                if (empty($gender)) {
                    return;
                }

                $contact->setGender(Genders::OTHER);
                $contact->setCustomGender($gender);
        }
    }
}
