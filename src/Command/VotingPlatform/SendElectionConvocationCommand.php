<?php

namespace App\Command\VotingPlatform;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Mailer\MailerService;
use App\Mailer\Message\TerritorialCouncilElectionConvocationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:voting-platform:send-convocation',
    description: 'Send convocation',
)]
class SendElectionConvocationCommand extends Command
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var MailerService */
    private $mailer;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    protected function configure(): void
    {
        $this
            ->addArgument('coterr-id', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        return self::SUCCESS;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    #[Required]
    public function setMailer(MailerService $transactionalMailer): void
    {
        $this->mailer = $transactionalMailer;
    }

    #[Required]
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }
}
