<?php

declare(strict_types=1);

namespace App\Service\Generator;

use App\Entity\KeyWord;

final class KeyWordCartesianProductGenerator
{
    /**
     * @param array<int, array<KeyWord>> $sets
     */
    public function __construct(
        private array $sets,
    ) {
        $this->sets = array_map('array_values', array_values($sets));
    }

    public function generate(): \Generator
    {
        if ($this->hasEmptySet()) {
            return (function (): \Generator {
                return yield from [];
            })();
        }

        return $this->build(0, []);
    }

    /**
     * @param array<int, string> $words
     */
    private function build(int $level, array $words): \Generator
    {
        if ($level === count($this->sets)) {
            yield implode(' ', $words);

            return;
        }

        foreach ($this->sets[$level] as $keyword) {
            yield from $this->build($level + 1, [...$words, $keyword->getWord()]);
        }
    }

    private function hasEmptySet(): bool
    {
        return array_any($this->sets, fn ($set) => $set === []);
    }
}
