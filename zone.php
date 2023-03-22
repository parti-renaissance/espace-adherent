<?php

use Doctrine\DBAL\DriverManager;
use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

require __DIR__.'/vendor/autoload.php';

$connectionParams = [
    'url' => 'mysql://root:root@127.0.0.1:3306/enmarche?charset=utf8',
];

$conn = DriverManager::getConnection($connectionParams);

$csv = Reader::createFromPath(__DIR__.'/zone_uuid_migration2.csv')->setDelimiter(';')->setHeaderOffset(0);

$io = new SymfonyStyle(
    new Symfony\Component\Console\Input\ArrayInput([]),
    new Symfony\Component\Console\Output\ConsoleOutput(OutputInterface::VERBOSITY_VERY_VERBOSE)
);

$io->progressStart(count($csv));

//$zonesFromDb = $conn->executeQuery('SELECT concat(TYPE, \'_\', code) AS id, code, uuid FROM geo_zone')->fetchAllAssociativeIndexed();

$zones = [];
foreach ($csv as $row) {
    $zones[] = $row;
    $io->progressAdvance();
}

$finder = Finder::create()->in(__DIR__.'/features')->name('*.feature')->ignoreDotFiles(true);
$toUpdate = [];

$files = [];
foreach ($finder->files() as $file) {
    $files[$file->getPathname()] = file_get_contents($file->getPathname());
}

foreach ($zones as $zone) {
    foreach ($files as $filePath => $fileContent) {
        if (str_contains($fileContent, $zone['old'])) {
            $toUpdate[] = $zone;
            file_put_contents($filePath, str_replace($zone['old'], $zone['new'], $fileContent));
        }
    }
}

dd($toUpdate);

//$writer = Writer::createFromPath(__DIR__.'/zone_to_update.csv', 'w')->setDelimiter(';');
//$writer->insertAll($toUpdate);
