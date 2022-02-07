<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MailchimpListInterestsCommand extends Command
{
    protected static $defaultName = 'app:mailchimp:list-interests';

    private $mailchimpClient;

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(HttpClientInterface $mailchimpClient)
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

            return 0;
        }

        $listData = $response->toArray();

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

        return 0;
    }

    private function getInterestCategories(string $listId): array
    {
        $url = sprintf('/3.0/lists/%s/interest-categories', $listId);

        return $this->mailchimpClient->request('GET', $url)->toArray();
    }

    private function getInterestCategoriesInterests(string $listId, string $categoryId): array
    {
        $url = sprintf('/3.0/lists/%s/interest-categories/%s/interests', $listId, $categoryId);

        return $this->mailchimpClient->request('GET', $url, [
            'query' => ['count' => 1000],
        ])->toArray();
    }
}
