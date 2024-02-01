<?php

namespace App\Adherent\Tag;

use Symfony\Contracts\Translation\TranslatorInterface;

class TagTranslator
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function trans(string $tag, bool $fullTag = true): string
    {
        if (substr_count($tag, ':')) {
            $parts = explode(':', $tag);

            foreach ($parts as $index => $part) {
                $matches = [];
                if (preg_match('/_(\d{4}):?/', $part, $matches)) {
                    $year = $matches[1];
                    $parts[$index] = $this->translator->trans('adherent.tag.'.str_replace('_'.$year, '_%s', $part), ['year' => $year]);
                } else {
                    $parts[$index] = $this->translator->trans('adherent.tag.'.$part);
                }
            }

            return $fullTag ? implode(' - ', $parts) : last($parts);
        }

        return $this->translator->trans('adherent.tag.'.$tag);
    }
}
