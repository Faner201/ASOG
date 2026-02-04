<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\SentenceRule;

interface SentenceRuleInterface
{
    public function apply(string $sentence): string;
}
