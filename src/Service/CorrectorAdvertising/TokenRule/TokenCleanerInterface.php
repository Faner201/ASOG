<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

interface TokenCleanerInterface
{
    public function clean(string $token): string;
}
