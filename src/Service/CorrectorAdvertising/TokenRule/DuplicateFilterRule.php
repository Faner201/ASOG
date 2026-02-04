<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

final class DuplicateFilterRule implements TokenRuleInterface
{
    public function apply(TokenContext $context): bool
    {
        if ($context->isDuplicate()) {
            return false;
        }

        return true;
    }
}
