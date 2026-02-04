<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

final class PositiveTokenFormatter
{
    public function format(string $core, string $sign): string
    {
        if ($sign !== '') {
            return $sign.$core;
        }

        if (mb_strlen($core) <= 2) {
            return '+'.$core;
        }

        return $core;
    }
}
