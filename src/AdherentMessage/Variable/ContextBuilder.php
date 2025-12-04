<?php

declare(strict_types=1);

namespace App\AdherentMessage\Variable;

use App\Entity\Adherent;

class ContextBuilder
{
    public function build(array $variables, Adherent $currentUser): array
    {
        $dictionary = array_column(Dictionary::getConfig(), null, 'code');

        $context = [];
        foreach ($variables as $variable) {
            if (empty($dictionary[$variable]) || empty($dictionary[$variable]['value']) || !\is_callable($dictionary[$variable]['value'])) {
                continue;
            }

            $context[$variable] = $dictionary[$variable]['value']($currentUser);
        }

        return $context;
    }
}
