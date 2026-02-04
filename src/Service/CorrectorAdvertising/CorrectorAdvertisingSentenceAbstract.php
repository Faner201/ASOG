<?php

namespace App\Service\CorrectorAdvertising;

abstract class CorrectorAdvertisingSentenceAbstract
{
    abstract protected function corrector(string $sentence): string;

    public function tokenize(string $sentence): array
    {
        $trimmed = trim($sentence);
        if ($trimmed === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', $trimmed);
        if ($parts === false) {
            return [];
        }

        return $parts;
    }
}
