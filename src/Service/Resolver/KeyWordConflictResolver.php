<?php

declare(strict_types=1);

namespace App\Service\Resolver;

use App\Entity\KeyWord;

final class KeyWordConflictResolver
{
    /**
     * @param KeyWord[] $keywords
     *
     * @return array<string, string[]>
     */
    public function resolve(array $keywords): array
    {
        $entries = [];

        foreach ($keywords as $keyword) {
            $tokens = $this->splitTokens($keyword->getWord());
            if ($tokens === []) {
                continue;
            }

            $entries[] = [
                'phrase'    => implode(' ', $tokens),
                'tokens'    => $tokens,
                'canonical' => array_map(
                    [$this, 'canonicalizeToken'],
                    $tokens
                ),
            ];
        }

        if ($entries === []) {
            return [];
        }

        $tokenIndex = $this->buildTokenIndex($entries);

        foreach ($entries as &$entry) {
            $entry['mask'] = $this->buildMask($entry['canonical'], $tokenIndex);
        }
        unset($entry);

        $result = [];

        foreach ($entries as $entry) {
            $negatives                = $this->collectNegativeTokens($entry, $entries, $tokenIndex);
            $result[$entry['phrase']] = array_map(
                static fn (string $token): string => '-'.$token,
                $negatives
            );
        }

        return $result;
    }

    /**
     * @return string[]
     */
    private function splitTokens(string $phrase): array
    {
        $cleaned = preg_replace('/-+/', ' ', $phrase);
        if ($cleaned === null) {
            $cleaned = '';
        }

        $trimmed = trim(preg_replace('/\s+/u', ' ', $cleaned));
        if ($trimmed === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', $trimmed);

        return $parts === false ? [] : $parts;
    }

    private function canonicalizeToken(string $token): string
    {
        return mb_strtoupper($token);
    }

    /**
     * @param array<string, array{phrase: string, tokens: string[], canonical: string[]}> $entries
     *
     * @return array<string, int>
     */
    private function buildTokenIndex(array $entries): array
    {
        $tokenIndex = [];
        $index      = 0;

        foreach ($entries as $entry) {
            foreach ($entry['canonical'] as $canonical) {
                if (!isset($tokenIndex[$canonical])) {
                    $tokenIndex[$canonical] = $index++;
                }
            }
        }

        return $tokenIndex;
    }

    /**
     * @param string[]           $canonicalTokens
     * @param array<string, int> $tokenIndex
     */
    private function buildMask(array $canonicalTokens, array $tokenIndex): \GMP
    {
        $mask = gmp_init(0);

        foreach ($canonicalTokens as $canonical) {
            if (!array_key_exists($canonical, $tokenIndex)) {
                continue;
            }

            gmp_setbit($mask, $tokenIndex[$canonical]);
        }

        return $mask;
    }

    /**
     * @param array<string, array{phrase: string, tokens: string[], canonical: string[], mask: \GMP}> $entries
     * @param array<string, int>                                                                      $tokenIndex
     *
     * @return string[]
     */
    private function collectNegativeTokens(array $current, array $entries, array $tokenIndex): array
    {
        $seen      = [];
        $negatives = [];

        foreach ($entries as $candidate) {
            if ($candidate['phrase'] === $current['phrase']) {
                continue;
            }

            if (!$this->isSuperset($candidate['mask'], $current['mask'])) {
                continue;
            }

            foreach ($candidate['canonical'] as $position => $canonical) {
                if (isset($seen[$canonical])) {
                    continue;
                }

                if (!array_key_exists($canonical, $tokenIndex)) {
                    continue;
                }

                if ($this->maskHasToken($current['mask'], $tokenIndex[$canonical])) {
                    continue;
                }

                $seen[$canonical] = true;
                $negatives[]      = $candidate['tokens'][$position];
            }
        }

        return $negatives;
    }

    private function isSuperset(\GMP $superset, \GMP $subset): bool
    {
        $intersection = gmp_and($superset, $subset);

        if (gmp_cmp($intersection, $subset) !== 0) {
            return false;
        }

        return gmp_cmp($superset, $subset) !== 0;
    }

    private function maskHasToken(\GMP $mask, int $position): bool
    {
        return gmp_testbit($mask, $position) == 1;
    }
}
