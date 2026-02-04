<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

final class EmptyCoreRule implements TokenRuleInterface
{
    public function apply(TokenContext $context): bool
    {
        return $context->getCore() !== '';
    }
}
