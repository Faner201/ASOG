<?php

namespace App\Service\CorrectorAdvertising\TokenRule;

final class TokenSignSplitter implements TokenCleanerInterface
{
    public function clean(string $token): string
    {
        $processed = preg_replace('/(?<!^)[+\-!]/u', ' ', $token);

        if ($processed === null) {
            return '';
        }

        return $processed;
    }
}
