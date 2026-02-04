<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

final readonly class PositiveFormattingRule implements TokenRuleInterface
{
    public function __construct(private PositiveTokenFormatter $formatter)
    {
    }

    public function apply(TokenContext $context): bool
    {
        $formatted = $this->formatter->format(
            $context->getCore(),
            $context->getSign()
        );

        $context->registerPositive($formatted);

        return false;
    }
}
