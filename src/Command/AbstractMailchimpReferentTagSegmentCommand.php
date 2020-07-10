<?php

namespace App\Command;

use App\Entity\MailchimpSegment;
use App\Repository\MailchimpSegmentRepository;
use App\Repository\ReferentTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractMailchimpReferentTagSegmentCommand extends Command
{
    protected const LIST_MAIN = 'main';
    protected const LIST_ELECTED_REPRESENTATIVE = 'elected_representative';

    protected const LISTS = [
        self::LIST_MAIN,
        self::LIST_ELECTED_REPRESENTATIVE,
    ];

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ReferentTagRepository
     */
    protected $referentTagRepository;

    /**
     * @var MailchimpSegmentRepository
     */
    protected $segmentRepository;

    protected $client;
    protected $mailchimpMainListId;
    protected $mailchimpElectedRepresentativeListId;
    /** @var SymfonyStyle */
    protected $io;

    public function __construct(
        ClientInterface $mailchimpClient,
        string $mailchimpMainListId,
        string $mailchimpElectedRepresentativeListId
    ) {
        $this->client = $mailchimpClient;
        $this->mailchimpMainListId = $mailchimpMainListId;
        $this->mailchimpElectedRepresentativeListId = $mailchimpElectedRepresentativeListId;

        parent::__construct();
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setReferentTagRepository(ReferentTagRepository $referentTagRepository): void
    {
        $this->referentTagRepository = $referentTagRepository;
    }

    /** @required */
    public function setSegmentRepository(MailchimpSegmentRepository $segmentRepository): void
    {
        $this->segmentRepository = $segmentRepository;
    }

    protected function configure()
    {
        $this
            ->addArgument('list', null, InputArgument::REQUIRED, implode('|', self::LISTS))
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function getSegments(string $list, int $offset, int $limit): array
    {
        $params = [
            'query' => [
                'offset' => $offset,
                'count' => $limit,
                'fields' => 'segments.id,segments.name',
            ],
        ];

        $response = $this->client->request('GET', sprintf('/3.0/lists/%s/segments', $this->getListId($list)), $params);

        if (200 !== $response->getStatusCode()) {
            return [];
        }

        return json_decode((string) $response->getBody(), true)['segments'];
    }

    protected function getListId(string $list): string
    {
        switch ($list) {
            case self::LIST_MAIN:
                return $this->mailchimpMainListId;
            case self::LIST_ELECTED_REPRESENTATIVE:
                return $this->mailchimpElectedRepresentativeListId;
            default:
                throw new \InvalidArgumentException(sprintf('List "%s"" is invalid. Available lists are: "%s".', $list, implode('", "', self::LISTS)));
        }
    }

    protected function findSegment(string $list, string $label): ?MailchimpSegment
    {
        return $this->segmentRepository->findOneBy([
            'list' => $list,
            'label' => $label,
        ]);
    }
}
