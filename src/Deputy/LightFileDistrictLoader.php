<?php

namespace App\Deputy;

use App\Entity\District;
use App\Entity\GeoData;
use App\Geo\GeometryFactory;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class used to load districts using as $districtsFile the file /src/DataFixtures/deputy/france_circonscriptions_legislatives.json
 * or https://www.data.gouv.fr/s/resources/carte-des-circonscriptions-legislatives-2012-et-2017/20170721-135742/france-circonscriptions-legislatives-2012.json
 * Actually used for loading fixtures.
 */
class LightFileDistrictLoader
{
    private $em;
    private $logger;
    private $decoder;
    private $doctrine;
    private $geoCountries;
    private $geoDistricts;
    private $geometryFactory;

    public function __construct(Registry $doctrine, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->geometryFactory = new GeometryFactory();
        $this->em = $this->doctrine->getManager();
        $this->decoder = new Serializer([new ObjectNormalizer()], [new JsonEncoder(), new CsvEncoder()]);
    }

    public function load(string $file, string $districtsFile, string $countriesFile): void
    {
        $districts = $this->decoder->decode(file_get_contents($file), 'csv', [CsvEncoder::DELIMITER_KEY => ';']);
        $this->geoDistricts = $this->decoder->decode(file_get_contents($districtsFile), 'json')['features'];
        $this->geoCountries = $this->decoder->decode(file_get_contents($countriesFile), 'json')['features'];

        $this->batchInsertOrUpdateDistricts($districts);
    }

    public function batchInsertOrUpdateDistricts(array $districts): void
    {
        $this->logger->notice(sprintf('%s districts are about to be loaded', \count($districts)));

        $i = 0;

        foreach ($districts as $district) {
            $this->createOrUpdateDistrict($district);
            $this->em->persist($this->createOrUpdateDistrict($district));
            $this->em->flush();

            if (0 === ++$i % 1000 || $i === \count($districts)) {
                $this->em->clear();
                $this->logger->notice("$i districts processed");
            }
        }
    }

    private function createOrUpdateDistrict(array $district): District
    {
        if (District::FRANCE === $district['code_pays']) {
            $json = ['ZA', 'ZB', 'ZC', 'ZD', 'ZS', 'ZM', 'ZX', 'ZW', 'ZP', 'ZN'];
            $csv = ['971', '972', '973', '974', '975', '976', '977', '986', '987', '988'];
            $code = str_replace($csv, $json, $district['circo_ID']);

            $geoDistricts = array_filter($this->geoDistricts, function ($geoDistrict) use ($code) {
                return $geoDistrict['properties']['ID'] === $code;
            });

            if (0 === \count($geoDistricts)) {
                throw new \RuntimeException("Districts GeoJSON file doesn't contain district with code '$code'");
            }
            $geoDistrict = array_shift($geoDistricts);

            $geoData = new GeoData($this->geometryFactory->createGeometryFromGeoJson($geoDistrict['geometry']));
            $countries = [District::FRANCE];
        } else {
            $countries = explode(',', str_replace(' ', '', $district['code_pays']));
            $geoCountries = array_filter($this->geoCountries, function ($country) use ($countries) {
                return \in_array($country['properties']['code'], $countries);
            });

            if (0 === \count($geoCountries)) {
                throw new \RuntimeException("Countries GeoJSON file doesn't contain countries with codes '$countries'");
            }
            $geoData = new GeoData($this->geometryFactory->mergeGeoJsonGeometries($geoCountries));
        }

        if ($existingDistrict = $this->em->getRepository(District::class)->findOneBy(['code' => $district['circo_ID']])) {
            return $existingDistrict->update(
                $countries,
                $district['nom_circo'],
                $geoData
            );
        }

        return new District(
            $countries,
            $district['nom_circo'],
            $district['circo_ID'],
            (int) $district['num_circo'],
            $district['code_dpt'],
            $geoData
        );
    }
}
