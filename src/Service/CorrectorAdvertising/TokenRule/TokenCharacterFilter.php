<?php

namespace App\Service\CorrectorAdvertising\TokenRule;

final class TokenCharacterFilter implements TokenCleanerInterface
{
    public function clean(string $token): string
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}+\-!]+/u', ' ', $token);

        if ($cleaned === null) {
            return '';
        }

        return $cleaned;
    }
}
