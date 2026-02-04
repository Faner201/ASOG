<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

final class NormalizedCoreRule implements TokenRuleInterface
{
    public function apply(TokenContext $context): bool
    {
        $context->setNormalized(mb_strtolower($context->getCore()));

        return true;
    }
}
