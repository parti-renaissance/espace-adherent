<?php

namespace AppBundle\Command;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\IdeaFinalizeMessage;
use AppBundle\Repository\IdeaRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotifyIdeaAuthorCommand extends Command
{
    protected static $defaultName = 'idea-workshop:notification:idea-author';

    private $ideaRepository;
    private $urlGenerator;
    private $mailer;

    public function __construct(IdeaRepository $ideaRepository, UrlGeneratorInterface $urlGenerator, MailerService $mailer)
    {
        $this->ideaRepository = $ideaRepository;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('caution', null, InputOption::VALUE_NONE, 'Send an email 3 days before the note finished date')
            ->addOption('delay', null, InputOption::VALUE_REQUIRED, 'Delay in minute (30min default)', 30)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ideas = $this->getIdeas(...$this->prepareDates(
            (int) $input->getOption('delay'),
            $isCautionMode = $input->getOption('caution')
        ));

        foreach ($ideas as $idea) {
            $this->sendMail($idea, $isCautionMode);
        }
    }

    /**
     * @return Idea[]
     */
    private function getIdeas(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->ideaRepository->createQueryBuilder('idea')
            ->where('idea.finalizedAt IS NOT NULL')
            ->andWhere(':start_date <= idea.finalizedAt AND idea.finalizedAt < :end_date')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->getQuery()
            ->getResult()
        ;
    }

    private function prepareDates(int $delay, bool $isCautionMode): array
    {
        $limitDate = new \DateTimeImmutable($isCautionMode ? '+3 days' : 'now');

        return [
            $limitDate->modify(sprintf('-%d minutes', $delay)),
            $limitDate,
        ];
    }

    private function sendMail(Idea $idea, bool $isCautionMode): void
    {
        if ($isCautionMode) {
            $message = IdeaFinalizeMessage::createPreNotification(
                $idea->getAuthor(),
                $this->urlGenerator->generate(
                    'react_app_ideas_workshop_proposition',
                    ['id' => $idea->getUuidAsString()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        } else {
            $message = IdeaFinalizeMessage::createNotification(
                $idea->getAuthor(),
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
