<?php

namespace App\Exporter;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Coalition\Filter\CauseFilter;
use App\Entity\Adherent;
use App\Entity\Coalition\Cause;
use App\Entity\PostAddress;
use App\Repository\Coalition\CauseRepository;
use App\Repository\Geo\CityRepository;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class CausesExporter
{
    private $coalitionsHost;
    private $exporter;
    private $repository;
    private $cityRepository;
    private $urlGenerator;
    private $translator;

    public function __construct(
        string $coalitionsHost,
        SonataExporter $exporter,
        CauseRepository $repository,
        CityRepository $cityRepository,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->coalitionsHost = $coalitionsHost;
        $this->exporter = $exporter;
        $this->cityRepository = $cityRepository;
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public function getResponse(string $format, CauseFilter $filter): Response
    {
        $array = new \ArrayObject($this->repository->getForExport($filter));

        return $this->exporter->getResponse(
            $format,
            sprintf('causes--%s.%s', date('d-m-Y--H-i'), $format),
            new IteratorCallbackSourceIterator(
                $array->getIterator(),
                function (Cause $cause) {
                    $author = $cause->getAuthor();

                    return [
                        'Id' => $cause->getId(),
                        'Statut' => $this->translator->trans('cause.'.$cause->getStatus()),
                        'Coalition' => $cause->getCoalition()->getName(),
                        'Zone' => $this->getZone($author),
                        'Ville' => $author->getCityName(),
                        'Soutiens' => $cause->getFollowersCount(),
                        'Objectif' => $cause->getName(),
                        'Description' => $cause->getDescription(),
                        'Adhérent' => $author->isAdherent() ? 'Oui' : 'Non',
                        'Prénom' => $author->getFirstName(),
                        'Nom' => $author->getLastName(),
                        'Email' => $author->getEmailAddress(),
                        'Image' => $cause->getImageName() ? $this->urlGenerator->generate(
                            'asset_url',
                            ['path' => $cause->getImagePath()],
                            \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
                        ) : null,
                        'Url' => \sprintf('%s/causes/%s', $this->coalitionsHost, $cause->getUuid()),
                        'Date de création' => $cause->getCreatedAt()->format('d/m/Y H:i:s'),
                    ];
                },
            )
        );
    }

    private function getZone(Adherent $adherent): ?string
    {
        if (!$adherent->getSource()) {
            return $adherent->getPostalCode();
        }

        if (PostAddress::FRANCE !== $adherent->getCountry()) {
            return $adherent->getCountry();
        }

        $city = $this->cityRepository->findOneBy(['code' => $adherent->getInseeCode()]);

        return $city ? $city->getPostalCodeAsString() : null;
    }
}
