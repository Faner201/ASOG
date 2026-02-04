<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

final class SignExtractionRule implements TokenRuleInterface
{
    public function apply(TokenContext $context): bool
    {
        $token = $context->getRawToken();
        $first = mb_substr($token, 0, 1);

        $sign = in_array($first, ['+', '-', '!'], true) ? $first : '';
        $context->setSign($sign);

        $core = $sign === '' ? $token : mb_substr($token, 1);
        $context->setCore($core);

        return true;
    }
}
