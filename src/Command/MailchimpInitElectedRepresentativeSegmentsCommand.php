<?php

namespace App\Command;

use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\MailchimpSegment;
use App\Entity\ReferentTag;
use App\Entity\UserListDefinitionEnum;
use App\Mailchimp\Synchronisation\ElectedRepresentativeTagsBuilder;
use App\Repository\MailchimpSegmentRepository;
use App\Repository\ReferentTagRepository;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailchimpInitElectedRepresentativeSegmentsCommand extends Command
{
    protected static $defaultName = 'mailchimp:elected-representative:init-tags';

    private $mailchimpSegmentRepository;
    private $referentTagRepository;
    private $tagsBuilder;
    private $client;
    private $entityManager;
    private $mailchimpElectedRepresentativeListId;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        MailchimpSegmentRepository $mailchimpSegmentRepository,
        ReferentTagRepository $referentTagRepository,
        ElectedRepresentativeTagsBuilder $tagsBuilder,
        ClientInterface $mailchimpClient,
        ObjectManager $entityManager,
        string $mailchimpElectedRepresentativeListId
    ) {
        $this->mailchimpSegmentRepository = $mailchimpSegmentRepository;
        $this->referentTagRepository = $referentTagRepository;
        $this->tagsBuilder = $tagsBuilder;
        $this->client = $mailchimpClient;
        $this->entityManager = $entityManager;
        $this->mailchimpElectedRepresentativeListId = $mailchimpElectedRepresentativeListId;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Sync Referent tag with Mailchimp (create Mailchimp tag)');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $referentTags = $this->referentTagRepository->findAll();
        $this->io->progressStart($countAllTags = \count($referentTags));

        /** @var ReferentTag $tag */
        foreach ($referentTags as $tag) {
            $this->initTag($tag->getCode());

            $this->io->progressAdvance();
        }

        foreach (MandateTypeEnum::CHOICES as $mandate) {
            $this->initTag($this->tagsBuilder->translateKey($mandate));

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

        foreach (UserListDefinitionEnum::CODES_ELECTED_REPRESENTATIVE as $userListDefinition) {
            $this->initTag($this->tagsBuilder->translateKey($userListDefinition));

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->note(sprintf(
            'Tags synchronized ! Maybe run "%s" to ensure both ways are synced.',
            'mailchimp:sync:segments elected_representative'
        ));
    }

    private function initTag(string $label): void
    {
        if (!$mailchimpSegment = $this->mailchimpSegmentRepository->findOneForElectedRepresentative($label)) {
            $mailchimpSegment = MailchimpSegment::createElectedRepresentativeSegment($label);
            $this->entityManager->persist($mailchimpSegment);
        }

        $url = sprintf('/3.0/lists/%s/segments', $this->mailchimpElectedRepresentativeListId);

        try {
            $response = $this->client->request('POST', $url, ['json' => [
                'name' => $mailchimpSegment->getLabel(),
                'static_segment' => [],
            ]]);
        } catch (RequestException $e) {
            $this->io->warning($e->getRequest() ? (string) $e->getResponse()->getBody() : $e->getMessage());
            $this->io->progressAdvance();

            return;
        }

        if (200 === $response->getStatusCode()) {
            $data = json_decode((string) $response->getBody(), true);

            if (isset($data['id'])) {
                $mailchimpSegment->setExternalId($data['id']);
                $this->entityManager->flush();
            }
        }
    }
}
