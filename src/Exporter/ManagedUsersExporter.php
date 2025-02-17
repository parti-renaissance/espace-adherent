<?php

namespace App\Exporter;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Entity\Geo\Zone;
use App\Entity\Projection\ManagedUser;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use App\Utils\PhoneNumberUtils;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagedUsersExporter
{
    public function __construct(
        private readonly SonataExporter $exporter,
        private readonly ManagedUserRepository $repository,
        private readonly TagTranslator $tagTranslator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getResponse(string $format, ManagedUsersFilter $filter): Response
    {
        $array = new \ArrayObject($this->repository->getExportQueryBuilder($filter)->getResult());

        return $this->exporter->getResponse(
            $format,
            \sprintf('adherents--%s.%s', date('d-m-Y--H-i'), $format),
            new IteratorCallbackSourceIterator(
                $array->getIterator(),
                function (ManagedUser $managedUser) {
                    return [
                        'PID' => $managedUser->publicId,
                        'Civilité' => $managedUser->getCivilityLabel(),
                        'Prénom' => $managedUser->getFirstName(),
                        'Nom' => $managedUser->getLastName(),
                        'Date de naissance' => $managedUser->getBirthdate()?->format('d/m/Y'),
                        'Téléphone' => PhoneNumberUtils::format($managedUser->getPhone()),
                        'Comité' => $managedUser->getCommittee(),
                        'Circonscription' => ($managedUser->getZonesOfType(Zone::DISTRICT)[0] ?? null)?->getNameCode(),
                        'Rôles' => implode(', ', array_map(fn (array $role) => $this->translator->trans('role.'.$role['role']), $managedUser->getRolesAsArray())),
                        'Labels Adhérent' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($managedUser->tags ?? [], fn (string $tag) => str_starts_with($tag, TagEnum::ADHERENT) || str_starts_with($tag, TagEnum::SYMPATHISANT)))),
                        'Labels Élu' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($managedUser->tags ?? [], fn (string $tag) => str_starts_with($tag, TagEnum::ELU)))),
                        'Déclaration de mandats' => implode(', ', $managedUser->getDeclaredMandates()),
                        'Mandats' => implode(', ', $managedUser->getMandates()),
                        'Labels Divers' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($managedUser->tags ?? [], fn (string $tag) => !str_starts_with($tag, TagEnum::ADHERENT) && !str_starts_with($tag, TagEnum::SYMPATHISANT) && !str_starts_with($tag, TagEnum::ELU)))),
                        'Date de création de compte' => $managedUser->getCreatedAt()?->format('d/m/Y H:i:s'),
                        'Date de première cotisation' => $managedUser->firstMembershipDonation?->format('d/m/Y H:i:s'),
                        'Date de dernière cotisation' => $managedUser->lastMembershipDonation?->format('d/m/Y H:i:s'),
                        'Date de dernière connexion' => $managedUser->lastLoggedAt?->format('d/m/Y H:i:s'),
                    ];
                }
            )
        );
    }
}
