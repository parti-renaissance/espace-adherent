<?php

namespace AppBundle\Deputy;

use AppBundle\Entity\District;
use AppBundle\Entity\GeoData;
use AppBundle\Geo\GeometryFactory;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DistrictLoader
{
    private $em;
    private $logger;
    private $decoder;
    private $doctrine;
    private $geoCountries;
    private $geoDistricts;

    public function __construct(Registry $doctrine, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->em = $this->doctrine->getManager();
        $this->decoder = new Serializer([new ObjectNormalizer()], [new JsonEncoder(), new CsvEncoder()]);
    }

    public function load(string $file, string $districtsFile, string $countriesFile): void
    {
        if (false !== $fileContent = file_get_contents($file)) {
            $districts = $this->decoder->decode($fileContent, 'csv', [CsvEncoder::DELIMITER_KEY => ';']);
        } else {
            throw new \Exception("\"$file\" is not a file.");
        }

        if (false !== $fileContent = file_get_contents($districtsFile)) {
            $this->geoDistricts = $this->decoder->decode($fileContent, 'json');
        } else {
            throw new \Exception("\"$districtsFile\" is not a file.");
        }

        if (false !== $fileContent = file_get_contents($countriesFile)) {
            $this->geoCountries = $this->decoder->decode($fileContent, 'json')['features'];
        } else {
            throw new \Exception("\"$countriesFile\" is not a file.");
        }

        $this->batchInsertOrUpdateDistricts($districts);
    }

    public function batchInsertOrUpdateDistricts(array $districts): void
    {
        $this->logger->notice(sprintf('%s districts are about to be loaded', \count($districts)));

        $i = 0;

        foreach ($districts as $district) {
            $this->em->persist($this->createOrUpdateDistrict($district));
            $this->em->flush();

            if (0 === ++$i % 100 || $i === \count($districts)) {
                $this->em->clear();
                $this->logger->notice("$i districts processed");
            }
        }
    }

    private function createOrUpdateDistrict(array $district): District
    {
        if (District::FRANCE === $district['code_pays']) {
            $codeDepartement = $district['code_dpt'];
            $number = (int) $district['num_circo'];

            $geoDistricts = array_filter($this->geoDistricts, function ($geoDistrict) use ($codeDepartement, $number) {
                return $geoDistrict['fields']['departement'] === $codeDepartement && (int) $geoDistrict['fields']['circonscription'] === $number;
            });

            if (0 === \count($geoDistricts)) {
                throw new \RuntimeException("Districts GeoJSON file doesn't contain district with number '$number' and department code '$codeDepartement'");
            }
            $key = key($geoDistricts);
            unset($this->geoDistricts[$key]);
            $geoDistrict = array_shift($geoDistricts);

            $geoData = new GeoData(GeometryFactory::createGeometryFromGeoJson($geoDistrict['fields']['geo_shape']));
            $countries = [District::FRANCE];
        } else {
            $countries = explode(',', str_replace(' ', '', $district['code_pays']));
            $geoCountries = array_filter($this->geoCountries, function ($country) use ($countries) {
                return \in_array($country['properties']['code'], $countries);
            });

            if (0 === \count($geoCountries)) {
                throw new \RuntimeException("Countries GeoJSON file doesn't contain countries with codes '$countries'");
            }
            $geoData = new GeoData(GeometryFactory::mergeGeoJsonGeometries($geoCountries));
        }

        if ($existingDistrict = $this->em->getRepository(District::class)->findOneBy(['code' => $district['circo_ID']])) {
            $this->em->remove($existingDistrict->getGeoData());

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
