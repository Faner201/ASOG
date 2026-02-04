<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

final class NegationRule implements TokenRuleInterface
{
    public function apply(TokenContext $context): bool
    {
        if ($context->getSign() !== '-') {
            return true;
        }

        if ($context->isDuplicate()) {
            return false;
        }

        $context->registerNegative();

        return false;
    }
}
