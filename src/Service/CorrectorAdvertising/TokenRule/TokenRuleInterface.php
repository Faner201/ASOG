<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

interface TokenRuleInterface
{
    public function apply(TokenContext $context): bool;
}
