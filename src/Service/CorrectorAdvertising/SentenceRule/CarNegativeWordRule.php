<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\SentenceRule;

final class CarNegativeWordRule implements SentenceRuleInterface
{
    /**
     * @var array<string, string[]>
     */
    private array $carNegativeWords = [];

    public function setCarNegativeWords(array $carNegativeWords): void
    {
        $this->carNegativeWords = array_reverse($carNegativeWords);
    }

    public function apply(string $sentence): string
    {
        if ($sentence === '' || $this->carNegativeWords === []) {
            return $sentence;
        }

        $extras = [];
        $seen   = [];

        foreach ($this->carNegativeWords as $phrase => $negatives) {
            if (!$this->sentenceContainsPhrase($sentence, $phrase)) {
                continue;
            }

            if ($negatives === []) {
                break;
            }

            foreach ($negatives as $negative) {
                if (isset($seen[$negative])) {
                    continue;
                }

                $seen[$negative] = true;
                $extras[]        = $negative;
            }

            break;
        }

        if ($extras === []) {
            return $sentence;
        }

        return trim($sentence.' '.implode(' ', $extras));
    }

    private function sentenceContainsPhrase(string $sentence, string $phrase): bool
    {
        if ($sentence === '' || $phrase === '') {
            return false;
        }

        return mb_stripos($sentence, $phrase) !== false;
    }
}
