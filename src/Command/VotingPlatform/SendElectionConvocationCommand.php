<?php

namespace App\Command\VotingPlatform;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Mailer\MailerService;
use App\Mailer\Message\TerritorialCouncilElectionConvocationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendElectionConvocationCommand extends Command
{
    protected static $defaultName = 'app:voting-platform:send-convocation';

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var MailerService */
    private $mailer;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    protected function configure()
    {
        $this
            ->addArgument('coterr-id', InputArgument::REQUIRED)
            ->setDescription('Send convocation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var TerritorialCouncil $territorialCouncil */
        if (!$territorialCouncil = $this->entityManager->getRepository(TerritorialCouncil::class)->find($input->getArgument('coterr-id'))) {
            throw new RuntimeException('CoTerr not found');
        }

        $membershipCollection = $territorialCouncil->getMemberships();

        if (!$president = $membershipCollection->getPresident()) {
            throw new RuntimeException('CoTerr without president');
        }

        $this->mailer->sendMessage(TerritorialCouncilElectionConvocationMessage::create(
            $territorialCouncil,
            $membershipCollection->toArray(),
            $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $president
        ));

        return 0;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setMailer(MailerService $transactionalMailer): void
    {
        $this->mailer = $transactionalMailer;
    }

    /** @required */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }
}
