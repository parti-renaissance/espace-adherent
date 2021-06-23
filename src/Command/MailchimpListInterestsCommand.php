<?php

namespace App\Command;

use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailchimpListInterestsCommand extends Command
{
    protected static $defaultName = 'app:mailchimp:list-interests';

    private $mailchimpClient;

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(ClientInterface $mailchimpClient)
    {
        $this->mailchimpClient = $mailchimpClient;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('List Mailchimp list interests')
            ->addArgument('listId', InputArgument::REQUIRED, 'The mailchimp list id')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $listId = $input->getArgument('listId');

        $response = $this->mailchimpClient->request('GET', sprintf('/3.0/lists/%s', $listId));

        if (200 !== $response->getStatusCode()) {
            $this->io->error(sprintf('No Mailchimp list found with id "%s".', $listId));

            return;
        }

        $listData = json_decode((string) $response->getBody(), true);

        $this->io->title(sprintf('Listing "%s" interests', $listData['name']));

        $categoriesData = $this->getInterestCategories($listId);

        foreach ($categoriesData['categories'] as $category) {
            $this->io->section($category['title']);

            $interestsData = $this->getInterestCategoriesInterests($listId, $category['id']);

            $rows = [];
            foreach ($interestsData['interests'] as $interest) {
                $rows[] = [$interest['name'], $interest['id']];
            }

            $this->io->table(['Nom', 'ID'], $rows);
        }
    }

    private function getInterestCategories(string $listId): array
    {
        $url = sprintf('/3.0/lists/%s/interest-categories', $listId);

        $response = $this->mailchimpClient->request('GET', $url);

        return json_decode((string) $response->getBody(), true);
    }

    private function getInterestCategoriesInterests(string $listId, string $categoryId): array
    {
        $url = sprintf('/3.0/lists/%s/interest-categories/%s/interests', $listId, $categoryId);

        $response = $this->mailchimpClient->request('GET', $url, [
            'query' => ['count' => 1000],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
