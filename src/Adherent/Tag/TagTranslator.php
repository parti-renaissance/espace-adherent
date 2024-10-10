<?php

namespace App\Adherent\Tag;

use App\Adherent\Tag\StaticTag\EventTagBuilder;
use App\Entity\AdherentStaticLabel;
use App\Entity\AdherentStaticLabelCategory;
use App\Repository\AdherentStaticLabelCategoryRepository;
use App\Repository\AdherentStaticLabelRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class TagTranslator
{
    private array $staticLabelCategories = [];
    private array $staticLabels = [];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EventTagBuilder $eventTagBuilder,
        private readonly AdherentStaticLabelCategoryRepository $staticLabelCategoryRepository,
        private readonly AdherentStaticLabelRepository $staticLabelRepository,
    ) {
    }

    public function trans(string $tag, bool $fullTag = true): string
    {
        if (substr_count($tag, ':')) {
            $this->loadStaticLabels();

            $parts = explode(':', $tag);

            foreach ($parts as $index => $part) {
                if (0 === $index && \array_key_exists($part, $this->staticLabelCategories)) {
                    $parts[$index] = $this->staticLabelCategories[$part];

                    continue;
                }

                if (1 === $index && \array_key_exists($part, $this->staticLabels)) {
                    $parts[$index] = $this->staticLabels[$part];

                    continue;
                }

                if ($negation = str_ends_with($part, '--')) {
                    $part = substr($part, 0, -2);
                }

                $matches = [];
                // Matches a year in the format _YYYY or _YYYY:
                if (preg_match('/_(\d{4}):?/', $part, $matches)) {
                    $year = $matches[1];
                    $parts[$index] = $this->translator->trans('adherent.tag.'.str_replace('_'.$year, '_%s', $part), ['year' => $year]);
                }
                // Matches a national_event tag in the format national_event:slug
                elseif (TagEnum::NATIONAL_EVENT === TagEnum::getMainLevel($tag) && $index > 0) {
                    $parts[$index] = $this->eventTagBuilder->buildLabelFromSlug($part);
                } else {
                    $parts[$index] = $this->translate('adherent.tag.'.$part, $part);
                }

                if ($negation) {
                    $parts[$index] = $this->translator->trans('adherent.tag.--negation--.'.TagEnum::getMainLevel($tag)).' '.$parts[$index];
                }
            }

            return $fullTag ? implode(' - ', $parts) : end($parts);
        }

        return $this->translate('adherent.tag.'.$tag, $tag);
    }

    private function translate(string $fullKey, string $part): string
    {
        $trans = $this->translator->trans($fullKey);

        if ($trans === $fullKey) {
            return ucfirst($part);
        }

        return $trans;
    }

    private function loadStaticLabels(): void
    {
        if (empty($this->staticLabelCategories)) {
            /** @var AdherentStaticLabelCategory[] $staticLabelCategories */
            $staticLabelCategories = $this->staticLabelCategoryRepository->findAll();

            foreach ($staticLabelCategories as $staticLabelCategory) {
                $this->staticLabelCategories[$staticLabelCategory->code] = $staticLabelCategory->label;
            }
        }

        if (empty($this->staticLabels)) {
            /** @var AdherentStaticLabel[] $staticLabels */
            $staticLabels = $this->staticLabelRepository->findAll();

            foreach ($staticLabels as $staticLabel) {
                $this->staticLabels[$staticLabel->code] = $staticLabel->label;
            }
        }
    }
}
