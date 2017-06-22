<?php

namespace AppBundle\Command;

use AppBundle\Entity\Article;
use AppBundle\Entity\Clarification;
use AppBundle\Entity\EntityContentInterface;
use AppBundle\Entity\Page;
use AppBundle\Entity\Proposal;
use Doctrine\ORM\EntityManager;
use League\CommonMark\CommonMarkConverter;
use Lullabot\AMP\AMP;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildAmpContentCommand extends ContainerAwareCommand
{
    const ENTITIES_TO_BUILD = [
        Article::class,
        Proposal::class,
        Clarification::class,
        Page::class,
    ];

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var CommonMarkConverter
     */
    private $markdown;

    protected function configure()
    {
        $this
            ->setName('app:amp:build')
            ->setDescription('Build the AMP version of all the content entities')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->markdown = $this->getContainer()->get(CommonMarkConverter::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach (self::ENTITIES_TO_BUILD as $entity) {
            $output->writeln('Synchronizing entity '.$entity.' ... ');
            $this->buildEntity($output, $entity);
            $output->write("\n");
        }
    }

    private function buildEntity(OutputInterface $output, $className): void
    {
        /** @var EntityContentInterface[] $items */
        $items = $this->manager->getRepository($className)->findAll();

        $progressbar = new ProgressBar($output, ceil(count($items) / 10));

        foreach ($items as $k => $item) {
            $html = $this->markdown->convertToHtml($item->getContent());

            $amp = new AMP();
            $amp->loadHtml($html);

            $item->setAmpContent($amp->convertToAmpHtml());

            if ($k % 10 === 0) {
                $progressbar->advance();
            }
        }

        $progressbar->finish();
        $this->manager->flush();
    }
}
