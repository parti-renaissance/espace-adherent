<?php

namespace App\Adherent\Tag;

use App\Adherent\Tag\StaticTag\TagBuilder;
use App\Repository\AdherentStaticLabelCategoryRepository;
use App\Repository\AdherentStaticLabelRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class TagTranslator
{
    private array $staticLabelCategories = [];
    private array $staticLabels = [];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TagBuilder $tagBuilder,
        private readonly AdherentStaticLabelCategoryRepository $staticLabelCategoryRepository,
        private readonly AdherentStaticLabelRepository $staticLabelRepository,
    ) {
    }

    public function trans(string $tag, bool $fullTag = true, string $domain = '_label_'): string
    {
        if (substr_count($tag, ':')) {
            $this->loadStaticLabels();

            $parts = explode(':', $tag);

            if (
                2 === \count($parts)
                && \array_key_exists($parts[0], $this->staticLabelCategories)
                && \array_key_exists($parts[1], $this->staticLabels)
            ) {
                return implode(' - ', [
                    $this->staticLabelCategories[$parts[0]],
                    $this->staticLabels[$parts[1]],
                ]);
            }

            foreach ($parts as $index => $part) {
                $matches = [];
                // Matches a year in the format _YYYY or _YYYY:
                if (preg_match('/_(\d{4}):?/', $part, $matches)) {
                    $year = $matches[1];
                    $parts[$index] = $this->translate(str_replace('_'.$year, '_%s', $part), $domain, $part, ['year' => $year]);
                }
                // Matches a national_event tag in the format national_event:slug or national_event:present:slug
                elseif (TagEnum::NATIONAL_EVENT === TagEnum::getMainLevel($tag) && $index > 0) {
                    if (str_starts_with($tag, TagEnum::NATIONAL_EVENT_PRESENT) && 1 === $index) {
                        continue;
                    }

                    $parts[$index] = $this->tagBuilder->buildLabelFromSlug($part);

                    if (str_starts_with($tag, TagEnum::NATIONAL_EVENT_PRESENT)) {
                        $parts[$index] = $this->translate($parts[$index - 1], $domain).' '.$parts[$index];
                        unset($parts[$index - 1]);
                    }
                } else {
                    $parts[$index] = $this->translate($part, $domain);
                }
            }

            return $fullTag ? implode(' - ', $parts) : end($parts);
        }

        return $this->translate($tag, $domain);
    }

    private function translate(string $key, string $domain, ?string $part = null, array $parameters = []): string
    {
        $pattern = 'adherent.tag%s.%s';

        $fullKey = \sprintf($pattern, '.'.$domain, $key);
        $fullKeyFallback = \sprintf($pattern, '', $key);

        $trans = $this->translator->trans($fullKey, $parameters = array_merge(['current_year' => date('Y')], $parameters));

        if ($trans === $fullKey) {
            $trans = $this->translator->trans($fullKeyFallback, $parameters);

            if ($trans === $fullKeyFallback) {
                return ucfirst($part ?? $key);
            }
        }

        return $trans;
    }

    private function loadStaticLabels(): void
    {
        if (empty($this->staticLabelCategories)) {
            $this->staticLabelCategories = $this->staticLabelCategoryRepository->findIndexedCodes();
        }

        if (empty($this->staticLabels)) {
            $this->staticLabels = $this->staticLabelRepository->findIndexedCodes();
        }
    }
}
