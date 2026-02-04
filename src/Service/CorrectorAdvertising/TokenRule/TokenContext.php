<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising\TokenRule;

final class TokenContext
{
    private string $token;
    private string $sign       = '';
    private string $core       = '';
    private string $normalized = '';

    /**
     * @var array<string, string>
     */
    private array $positive;

    /**
     * @var array<string, string>
     */
    private array $negative;

    /**
     * @var array<string, bool>
     */
    private array $positiveKeys;

    /**
     * @var array<string, bool>
     */
    private array $negativeKeys;

    /**
     * @param array<string, string> $positive
     * @param array<string, string> $negative
     * @param array<string, bool>   $positiveKeys
     * @param array<string, bool>   $negativeKeys
     */
    public function __construct(
        string $token,
        array &$positive,
        array &$negative,
        array &$positiveKeys,
        array &$negativeKeys,
    ) {
        $this->token        = $token;
        $this->positive     = &$positive;
        $this->negative     = &$negative;
        $this->positiveKeys = &$positiveKeys;
        $this->negativeKeys = &$negativeKeys;
    }

    public function getRawToken(): string
    {
        return $this->token;
    }

    public function setSign(string $sign): void
    {
        $this->sign = $sign;
    }

    public function getSign(): string
    {
        return $this->sign;
    }

    public function setCore(string $core): void
    {
        $this->core = $core;
    }

    public function getCore(): string
    {
        return $this->core;
    }

    public function setNormalized(string $normalized): void
    {
        $this->normalized = $normalized;
    }

    public function getNormalized(): string
    {
        return $this->normalized;
    }

    public function isDuplicate(): bool
    {
        $normalized = $this->normalized;

        if ($normalized === '') {
            return false;
        }

        return isset($this->positiveKeys[$normalized]) || isset($this->negativeKeys[$normalized]);
    }

    public function registerNegative(): void
    {
        $normalized = $this->normalized;

        if ($normalized === '') {
            return;
        }

        $this->negative[$normalized]     = '-'.$this->core;
        $this->negativeKeys[$normalized] = true;
    }

    public function registerPositive(string $value): void
    {
        $normalized = $this->normalized;

        if ($normalized === '') {
            return;
        }

        $this->positive[$normalized]     = $value;
        $this->positiveKeys[$normalized] = true;
    }
}
