<?php

namespace AppBundle\Command;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\IdeaContributionsMessage;
use AppBundle\Repository\IdeaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotifyIdeaAuthorAboutContributionsCommand extends Command
{
    protected static $defaultName = 'idea-workshop:notification:contributions';

    /**
     * @var SymfonyStyle
     */
    private $io;

    private $entityManager;
    private $ideaRepository;
    private $urlGenerator;
    private $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        IdeaRepository $ideaRepository,
        UrlGeneratorInterface $urlGenerator,
        MailerService $mailer
    ) {
        $this->entityManager = $entityManager;
        $this->ideaRepository = $ideaRepository;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption(
            'interval',
            null,
            InputOption::VALUE_REQUIRED,
            'Send an email to the idea author about contributions to his idea every X days (4 by default)',
            4
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (1 > $interval = (int) $input->getOption('interval')) {
            $this->io->error("Option 'interval' should be a valid integer different from zero.");

            return 1;
        }

        foreach ($this->getIdeas($interval) as $idea) {
            $this->sendMail($idea);
            $idea->setLastContributionNotificationDate(new \DateTime());
            $this->entityManager->flush();
        }
    }

    /**
     * @return Idea[]
     */
    private function getIdeas(int $days): array
    {
        $qb = $this->ideaRepository->createQueryBuilder('idea');

        return $qb->where('idea.publishedAt IS NOT NULL')
            ->andWhere(':now < idea.finalizedAt')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX('idea.lastContributionNotificationDate IS NULL', 'idea.publishedAt <= :date'),
                $qb->expr()->andX('idea.lastContributionNotificationDate IS NOT NULL', 'idea.lastContributionNotificationDate <= :date')
            ))
            ->setParameter('now', new \DateTime())
            ->setParameter('date', (new \DateTime("-$days days")))
            ->getQuery()
            ->getResult()
        ;
    }

    private function sendMail(Idea $idea): void
    {
        if ($idea->getCommentsCount() > 0) {
            $message = IdeaContributionsMessage::createWithContributions(
                $idea->getAuthor(),
                $idea->getName(),
                $this->urlGenerator->generate(
                    'react_app_ideas_workshop_proposition',
                    ['id' => $idea->getUuidAsString()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                $this->ideaRepository->countContributors($idea)['count'],
                $idea->getCommentsCount()
            );
        } else {
            $message = IdeaContributionsMessage::createWithoutContributions(
                $idea->getAuthor(),
                $idea->getName(),
                $this->urlGenerator->generate(
                    'react_app_ideas_workshop_proposition',
                    ['id' => $idea->getUuidAsString()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        }

        $this->mailer->sendMessage($message);
    }
}
