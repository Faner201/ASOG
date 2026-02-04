<?php

declare(strict_types=1);

namespace App\Service\CorrectorAdvertising;

use App\Service\CorrectorAdvertising\SentenceRule\CarNegativeWordRule;
use App\Service\CorrectorAdvertising\SentenceRule\SentenceRuleInterface;
use App\Service\CorrectorAdvertising\TokenRule\TokenCleanerInterface;
use App\Service\CorrectorAdvertising\TokenRule\TokenContext;
use App\Service\CorrectorAdvertising\TokenRule\TokenRuleInterface;

final class CorrectorAdvertisingSentence extends CorrectorAdvertisingSentenceAbstract
{
    /**
     * @var TokenRuleInterface[]
     */
    private array $tokenRules;

    /**
     * @var TokenCleanerInterface[]
     */
    private array $tokenCleaners;

    /**
     * @var SentenceRuleInterface[]
     */
    private array $sentenceRules;

    /**
     * @var array<string, string[]>
     */
    private array $carNegativeWords = [];

    /**
     * @param iterable<TokenRuleInterface>    $tokenRules
     * @param iterable<TokenCleanerInterface> $tokenCleaners
     */
    public function __construct(
        iterable $tokenRules,
        iterable $tokenCleaners,
    ) {
        $this->tokenRules    = array_values(iterator_to_array($tokenRules));
        $this->tokenCleaners = array_values(iterator_to_array($tokenCleaners));
        $this->sentenceRules = [];
    }

    /**
     * @param iterable<SentenceRuleInterface> $sentenceRules
     */
    public function setSentenceRules(iterable $sentenceRules): void
    {
        $this->sentenceRules = [];
        foreach ($sentenceRules as $sentenceRule) {
            $this->sentenceRules[] = $sentenceRule;
        }
    }

    public function setCarNegativeWords(array $carNegativeWords): void
    {
        $this->carNegativeWords = $carNegativeWords;

        foreach ($this->sentenceRules as $rule) {
            if ($rule instanceof CarNegativeWordRule) {
                $rule->setCarNegativeWords($carNegativeWords);
            }
        }
    }

    public function corrector(string $sentence): string
    {
        $tokens                = $this->tokenize($sentence);
        [$positive, $negative] = $this->correctTokens($tokens);

        $assembled = $this->assembleSentence($positive, $negative);
        foreach ($this->sentenceRules as $rule) {
            $assembled = $rule->apply($assembled);
        }

        return $assembled;
    }

    /** @param string[] $tokens
     * @return array<string[], string[]>
     */
    private function correctTokens(array $tokens): array
    {
        $positive     = [];
        $negative     = [];
        $positiveKeys = [];
        $negativeKeys = [];

        foreach ($tokens as $token) {
            foreach ($this->prepareSubTokens($token) as $subToken) {
                $context = new TokenContext(
                    $subToken,
                    $positive,
                    $negative,
                    $positiveKeys,
                    $negativeKeys,
                );

                foreach ($this->tokenRules as $rule) {
                    if (!$rule->apply($context)) {
                        break;
                    }
                }
            }
        }

        return [array_values($positive), array_values($negative)];
    }

    /** @return string[] */
    private function prepareSubTokens(string $token): array
    {
        $processed = $token;
        foreach ($this->tokenCleaners as $cleaner) {
            $processed = $cleaner->clean($processed);
        }

        return $this->tokenize($processed);
    }

    private function assembleSentence(array $positive, array $negative): string
    {
        $ordered = [...$positive, ...$negative];

        return trim(implode(' ', $ordered));
    }
}
