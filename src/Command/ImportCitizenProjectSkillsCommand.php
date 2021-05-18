<?php

namespace App\Command;

use App\Entity\CitizenProjectSkill;
use App\Repository\CitizenProjectSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCitizenProjectSkillsCommand extends Command
{
    public const CITIZEN_PROJECT_SKILLS = [
        'Juriste (conseil juridique)',
        'Juriste (droit des associations, sociétés…)',
        'Juriste (propriété intellectuelle)',
        'Juriste (droit de l’environnement)',
        'Juriste (contentieux)',
        'Juriste (rédaction d’actes juridiques)',
        'Spécialiste du financement',
        'Comptabilité',
        'Audit et conseil',
        'Graphiste (logo)',
        'Graphiste (brochures et supports de communication)',
        'Community manager',
        'Responsable logistique',
        'Chef de projet',
        'Marketing',
        'Communication',
        'Organisation d’événements',
        'Rédaction de contenus',
        'Conception de contenus vidéos et multimédias',
        'Conception d’enquêtes de satisfaction',
        'Chargé des relations presse',
        'Chargé des relations publiques',
        'Mailings',
        'Professeur du primaire',
        'Professeur du secondaire',
        'Professeur d’université',
        'Chercheur',
        'Éducateur et animateur spécialisé',
        'Professeur de sport',
        'Parent d’élèves',
        'Traducteur et linguiste',
        'Artiste',
        'Professionnel de la culture',
        'Architecte et designer',
        'Dessinateur, peintre et sculpteur',
        'Musicien',
        'Acteur, professionnel du théâtre et du cirque',
        'Photographe',
        'Professionnel des ressources humaines',
        'Professionnel de la réinsertion professionnelle',
        'Professionnel de la formation',
        'Professionnel de la réorientation professionnelle',
        'Responsable associatif',
        'Solidarité intergénérationnel',
        'Lutte contre l’exclusion',
        'Egalité des chances',
        'Lutte contre les discriminations femmes-hommes',
        'Lutte contre les discriminations LGBTQ+',
        'Handicap',
        'Migrant, demandeur d’asile et réfugiés',
        'Entrepreneur',
        'Développeur d’application',
        'Développeur de site internet',
        'Gestion de base de données',
        'Utilisation des outils bureautiques',
        'Spécialiste en robotique',
        'Spécialiste en intelligence artificielle',
        'Animateur de fablab',
        'Graphiste 3D',
        'Gaspillage alimentaire',
        'Énergies renouvelables',
        'Recyclage et gestion des déchets',
        'Agriculteur',
        'Jardinier et paysagiste',
        'Isolation thermique et acoustique',
        'Médecin',
        'Infirmier(e)',
        'Aide-soignant(e)',
        'Psychologue',
        'Secouriste',
        'Vétérinaire',
        'Artisan',
        'Construction et chantier',
        'Chef cuisinier',
        'Restaurateur et hôtelier',
        'Guide touristique',
    ];

    private $citizenProjectSkillsRepository;
    private $entityManager;

    protected function configure()
    {
        $this
            ->setName('app:import:citizen-project-skills')
            ->setDescription('Import a pre defined list of CitizenProjectSkill')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start import...');
        $countToAdd = 0;
        foreach (self::CITIZEN_PROJECT_SKILLS as $skillName) {
            if ($citizenProjectSkill = $this->citizenProjectSkillsRepository->findOneByName($skillName)) {
                continue;
            }

            $this->entityManager->persist(new CitizenProjectSkill($skillName));
            ++$countToAdd;
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('Finish : %s citizenProjectSkills was added', $countToAdd));
    }

    /** @required */
    public function setCitizenProjectSkillsRepository(
        CitizenProjectSkillRepository $citizenProjectSkillsRepository
    ): void {
        $this->citizenProjectSkillsRepository = $citizenProjectSkillsRepository;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
