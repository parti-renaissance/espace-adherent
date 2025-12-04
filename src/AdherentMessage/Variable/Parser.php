<?php

declare(strict_types=1);

namespace App\AdherentMessage\Variable;

class Parser
{
    public function extract(string $content): array
    {
        preg_match_all('/({{.*?}})/', $content, $matches);

        return array_values(array_intersect(array_column(Dictionary::getConfig(), 'code'), array_values(array_unique($matches[0]))));
    }
}
