<?php

namespace App\Command;

use App\Entity\CitizenProject;
use App\Entity\Committee;
use App\Entity\ReferentTag;
use App\Repository\CitizenProjectRepository;
use App\Repository\CommitteeRepository;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailchimpSegmentUpdateDbSegmentIdsCommand extends AbstractMailchimpReferentTagSegmentCommand
{
    protected static $defaultName = 'mailchimp:segment:update-db-segment-ids';

    private $committeeRepository;
    private $citizenProjectRepository;

    public function __construct(
        ClientInterface $mailchimpClient,
        string $mailchimpMainListId,
        string $mailchimpElectedRepresentativeListId,
        CommitteeRepository $committeeRepository,
        CitizenProjectRepository $citizenProjectRepository
    ) {
        parent::__construct($mailchimpClient, $mailchimpMainListId, $mailchimpElectedRepresentativeListId);

        $this->committeeRepository = $committeeRepository;
        $this->citizenProjectRepository = $citizenProjectRepository;
    }

    protected function configure()
    {
        $this->setDescription('Update Referent tags with Mailchimp external ids');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = $input->getArgument('list');

        $this->io->progressStart();

        $offset = 0;
        $limit = 1000;

        while ($tags = $this->getSegments($list, $offset, $limit)) {
            $this->updateReferentTags($tags, $list);
            if (self::LIST_MAIN === $list) {
                $this->updateCommittees($tags);
                $this->updateCitizenProjects($tags);
            }

            $offset += $limit;
        }

        $this->io->progressFinish();
    }

    private function updateReferentTags(array $segments, string $list): void
    {
        $iterator = $this->referentTagRepository->createQueryBuilder('tag')
            ->where('tag.externalId IS NULL')
            ->getQuery()
            ->iterate()
        ;

        foreach ($iterator as $refTag) {
            /** @var ReferentTag $refTag */
            $refTag = current($refTag);

            foreach ($segments as $tag) {
                if ($tag['name'] === $refTag->getCode()) {
                    switch ($list) {
                        case self::LIST_MAIN:
                            $refTag->setExternalId($tag['id']);

                            break;
                        case self::LIST_ELECTED_REPRESENTATIVE:
                            $refTag->setExternalElectedRepresentativeListId($tag['id']);

                            break;
                        default:
                            break;
                    }
                    $this->io->progressAdvance();
                    break;
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function updateCommittees(array $segments): void
    {
        $iterator = $this->committeeRepository->createQueryBuilder('committee')
            ->where('committee.mailchimpId IS NULL')
            ->andWhere('committee.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
            ->iterate()
        ;

        foreach ($iterator as $committee) {
            /** @var Committee $committee */
            $committee = current($committee);

            foreach ($segments as $tag) {
                if ($tag['name'] === $committee->getUuid()->toString()) {
                    $committee->setMailchimpId($tag['id']);

                    $this->io->progressAdvance();
                    break;
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function updateCitizenProjects(array $segments): void
    {
        $iterator = $this->citizenProjectRepository->createQueryBuilder('cp')
            ->where('cp.mailchimpId IS NULL')
            ->andWhere('cp.status = :status')
            ->setParameter('status', CitizenProject::APPROVED)
            ->getQuery()
            ->iterate()
        ;

        foreach ($iterator as $cp) {
            /** @var CitizenProject $cp */
            $cp = current($cp);

            foreach ($segments as $tag) {
                if ($tag['name'] === $cp->getUuid()->toString()) {
                    $cp->setMailchimpId($tag['id']);

                    $this->io->progressAdvance();
                    break;
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
