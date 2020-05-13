<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\District;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class BindDeputiesToDistrictsCommand extends Command
{
    protected static $defaultName = 'app:deputy-districts:bind';

    private $em;
    private $decoder;
    private $hasErrors = false;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file of deputies with their managed districts to load')
            ->setDescription('Bind deputies to districts. Attention! If district has a deputy it will be changed without warning.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->decoder = new Serializer([new ObjectNormalizer()], [new JsonEncoder(), new CsvEncoder()]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_readable($file = $input->getArgument('file'))) {
            $this->io->error("$file is not a file");

            return 1;
        }

        $deputies = $this->decoder->decode(file_get_contents($file), 'csv', [CsvEncoder::DELIMITER_KEY => ',']);
        $this->io->title(sprintf('Starting bind deputies to districts: %s deputies are about to be binded.', \count($deputies)));

        $this->em->beginTransaction();

        try {
            $this->bindDeputies($deputies);
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
            $this->hasErrors = true;
        }

        if ($this->hasErrors) {
            $this->em->rollback();
            $this->io->text('Errors occurred while command execution. Any deputy has been bound to district. Please consult the log above for more information.');

            return 1;
        }

        $this->em->commit();
        $this->io->success('Done: deputies have been successfully bound to districts.');
    }

    private function bindDeputies(array $deputies): void
    {
        foreach ($deputies as $deputy) {
            if (!$deputy['code_dpt'] || !$deputy['email']) {
                continue;
            }

            if ('FDE' === $deputy['code_dpt']) {
                $params = [
                    'code' => sprintf('FDE-%02d', $deputy['num_circo']),
                ];
            } elseif (1 === \strlen($deputy['code_dpt'])) {
                $params = [
                    'departmentCode' => sprintf('%02d', $deputy['code_dpt']),
                    'number' => (int) $deputy['num_circo'],
                ];
            } else {
                $params = [
                    'departmentCode' => $deputy['code_dpt'],
                    'number' => (int) $deputy['num_circo'],
                ];
            }

            $district = $this->em->getRepository(District::class)->findOneBy($params);
            if (!$district) {
                $this->io->error(sprintf(
                    "District with ID '%s' and department code '%s' does not exist. Impossible to bind a deputy with uuid '%s'.",
                    $deputy['num_circo'],
                    $deputy['code_dpt'],
                    $deputy['email']
                ));
                $this->hasErrors = true;
                continue;
            }

            $adherent = $this->em->getRepository(Adherent::class)->findOneBy(['emailAddress' => $deputy['email']]);
            if (!$adherent) {
                $this->io->error(sprintf("Adherent with email '%s' does not exist.", $deputy['email']));
                $this->hasErrors = true;
                continue;
            }
            $district->setAdherent($adherent);
            $this->em->persist($district);
        }

        $this->em->flush();
    }
}
