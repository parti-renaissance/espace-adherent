<?php

declare(strict_types=1);

namespace App\Adherent\Tag\TagGenerator;

use App\Entity\Adherent;

class AdherentStaticLabelTagGenerator extends AbstractTagGenerator
{
    public function generate(Adherent $adherent, array $previousTags): array
    {
        $tags = [];

        foreach ($adherent->getStaticLabels() as $staticLabel) {
            $category = $staticLabel->category;

            if (!$category->sync) {
                continue;
            }

            $tags[] = $staticLabel->getIdentifier();
        }

        return $tags;
    }
}
