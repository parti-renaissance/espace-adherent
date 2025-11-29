<?php

declare(strict_types=1);

namespace App\Command;

use App\Adherent\MandateTypeEnum;
use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\MailchimpSegment;
use App\Mailchimp\Synchronisation\ElectedRepresentativeTagsBuilder;
use App\Repository\MailchimpSegmentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'mailchimp:elected-representative:init-tags',
    description: 'Sync Referent tag with Mailchimp (create Mailchimp tag)',
)]
class MailchimpInitElectedRepresentativeSegmentsCommand extends Command
{
    private $mailchimpSegmentRepository;
    private $tagsBuilder;
    private $client;
    private $entityManager;
    private $mailchimpElectedRepresentativeListId;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        MailchimpSegmentRepository $mailchimpSegmentRepository,
        ElectedRepresentativeTagsBuilder $tagsBuilder,
        HttpClientInterface $mailchimpClient,
        ObjectManager $entityManager,
        string $mailchimpElectedRepresentativeListId,
    ) {
        $this->mailchimpSegmentRepository = $mailchimpSegmentRepository;
        $this->tagsBuilder = $tagsBuilder;
        $this->client = $mailchimpClient;
        $this->entityManager = $entityManager;
        $this->mailchimpElectedRepresentativeListId = $mailchimpElectedRepresentativeListId;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->progressStart();

        foreach (MandateTypeEnum::ALL as $mandateType) {
            $this->initTag($this->tagsBuilder->translateKey($mandateType));

            $this->io->progressAdvance();
        }

        foreach (PoliticalFunctionNameEnum::CHOICES as $politicalFunction) {
            $this->initTag($this->tagsBuilder->translateKey($politicalFunction));

            $this->io->progressAdvance();
        }

        foreach (LabelNameEnum::ALL as $label) {
            $this->initTag($this->tagsBuilder->translateKey($label));

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->note(\sprintf(
            'Tags synchronized ! Maybe run "%s" to ensure both ways are synced.',
            'mailchimp:sync:segments elected_representative'
        ));

        return self::SUCCESS;
    }

    private function initTag(string $label): void
    {
        if (!$mailchimpSegment = $this->mailchimpSegmentRepository->findOneForElectedRepresentative($label)) {
            $mailchimpSegment = MailchimpSegment::createElectedRepresentativeSegment($label);
            $this->entityManager->persist($mailchimpSegment);
        }

        $url = \sprintf('/3.0/lists/%s/segments', $this->mailchimpElectedRepresentativeListId);

        try {
            $response = $this->client->request('POST', $url, ['json' => [
                'name' => $mailchimpSegment->getLabel(),
                'static_segment' => [],
            ]]);
            $data = $response->toArray();
        } catch (ExceptionInterface $e) {
            $this->io->warning($e instanceof HttpExceptionInterface ? $e->getResponse()->getContent() : $e->getMessage());
            $this->io->progressAdvance();

            return;
        }

        if (200 === $response->getStatusCode()) {
            if (isset($data['id'])) {
                $mailchimpSegment->setExternalId($data['id']);
                $this->entityManager->flush();
            }
        }
    }
}
