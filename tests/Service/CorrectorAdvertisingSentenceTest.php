<?php

declare(strict_types=1);

namespace Service;

use App\Service\CorrectorAdvertising\CorrectorAdvertisingSentence;
use App\Service\CorrectorAdvertising\SentenceRule\CarNegativeWordRule;
use App\Service\CorrectorAdvertising\SentenceRule\SentenceRuleInterface;
use App\Service\CorrectorAdvertising\TokenRule\DuplicateFilterRule;
use App\Service\CorrectorAdvertising\TokenRule\EmptyCoreRule;
use App\Service\CorrectorAdvertising\TokenRule\NegationRule;
use App\Service\CorrectorAdvertising\TokenRule\NormalizedCoreRule;
use App\Service\CorrectorAdvertising\TokenRule\PositiveFormattingRule;
use App\Service\CorrectorAdvertising\TokenRule\PositiveTokenFormatter;
use App\Service\CorrectorAdvertising\TokenRule\SignExtractionRule;
use App\Service\CorrectorAdvertising\TokenRule\TokenCharacterFilter;
use App\Service\CorrectorAdvertising\TokenRule\TokenCleanerInterface;
use App\Service\CorrectorAdvertising\TokenRule\TokenRuleInterface;
use App\Service\CorrectorAdvertising\TokenRule\TokenSignSplitter;
use PHPUnit\Framework\TestCase;

final class CorrectorAdvertisingSentenceTest extends TestCase
{
    /**
     * @dataProvider sentenceProvider
     */
    public function testReturnsNormalizedSentence(string $sentence, string $expected): void
    {
        $service = new CorrectorAdvertisingSentence(
            self::createTokenRules(),
            self::createTokenCleaners()
        );
        $service->setSentenceRules(self::createSentenceRules());
        $service->setCarNegativeWords(self::createCarNegativeWords());
        self::assertSame($expected, $service->corrector($sentence));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function sentenceProvider(): array
    {
        return [
            'Honda Владивосток продажа' => [
                'Honda Владивосток продажа',
                'Honda Владивосток продажа -CRF -450X',
            ],
            'Honda Владивосток покупка' => [
                'Honda Владивосток покупка',
                'Honda Владивосток покупка -CRF -450X',
            ],
            'Honda Владивосток цена' => [
                'Honda Владивосток цена',
                'Honda Владивосток цена -CRF -450X',
            ],
            'Honda Владивосток с пробегом' => [
                'Honda Владивосток с пробегом',
                'Honda Владивосток +с пробегом -CRF -450X',
            ],
            'Honda Приморский край -Владивосток продажа' => [
                'Honda Приморский край -Владивосток продажа',
                'Honda Приморский край продажа -Владивосток -CRF -450X',
            ],
            'Honda Приморский край -Владивосток покупка' => [
                'Honda Приморский край -Владивосток покупка',
                'Honda Приморский край покупка -Владивосток -CRF -450X',
            ],
            'Honda Приморский край -Владивосток цена' => [
                'Honda Приморский край -Владивосток цена',
                'Honda Приморский край цена -Владивосток -CRF -450X',
            ],
            'Honda Приморский край -Владивосток с пробегом' => [
                'Honda Приморский край -Владивосток с пробегом',
                'Honda Приморский край +с пробегом -Владивосток -CRF -450X',
            ],
            'Honda CRF Владивосток продажа' => [
                'Honda CRF Владивосток продажа',
                'Honda CRF Владивосток продажа -450X',
            ],
            'Honda CRF Владивосток покупка' => [
                'Honda CRF Владивосток покупка',
                'Honda CRF Владивосток покупка -450X',
            ],
            'Honda CRF Владивосток цена' => [
                'Honda CRF Владивосток цена',
                'Honda CRF Владивосток цена -450X',
            ],
            'Honda CRF Владивосток с пробегом' => [
                'Honda CRF Владивосток с пробегом',
                'Honda CRF Владивосток +с пробегом -450X',
            ],
            'Honda CRF Приморский край -Владивосток продажа' => [
                'Honda CRF Приморский край -Владивосток продажа',
                'Honda CRF Приморский край продажа -Владивосток -450X',
            ],
            'Honda CRF Приморский край -Владивосток покупка' => [
                'Honda CRF Приморский край -Владивосток покупка',
                'Honda CRF Приморский край покупка -Владивосток -450X',
            ],
            'Honda CRF Приморский край -Владивосток цена' => [
                'Honda CRF Приморский край -Владивосток цена',
                'Honda CRF Приморский край цена -Владивосток -450X',
            ],
            'Honda CRF Приморский край -Владивосток с пробегом' => [
                'Honda CRF Приморский край -Владивосток с пробегом',
                'Honda CRF Приморский край +с пробегом -Владивосток -450X',
            ],
            'Honda CRF-450X Владивосток продажа' => [
                'Honda CRF-450X Владивосток продажа',
                'Honda CRF 450X Владивосток продажа',
            ],
            'Honda CRF-450X Владивосток покупка' => [
                'Honda CRF-450X Владивосток покупка',
                'Honda CRF 450X Владивосток покупка',
            ],
            'Honda CRF-450X Владивосток цена' => [
                'Honda CRF-450X Владивосток цена',
                'Honda CRF 450X Владивосток цена',
            ],
            'Honda CRF-450X Владивосток с пробегом' => [
                'Honda CRF-450X Владивосток с пробегом',
                'Honda CRF 450X Владивосток +с пробегом',
            ],
            'Honda CRF-450X Приморский край -Владивосток продажа' => [
                'Honda CRF-450X Приморский край -Владивосток продажа',
                'Honda CRF 450X Приморский край продажа -Владивосток',
            ],
            'Honda CRF-450X Приморский край -Владивосток покупка' => [
                'Honda CRF-450X Приморский край -Владивосток покупка',
                'Honda CRF 450X Приморский край покупка -Владивосток',
            ],
            'Honda CRF-450X Приморский край -Владивосток цена' => [
                'Honda CRF-450X Приморский край -Владивосток цена',
                'Honda CRF 450X Приморский край цена -Владивосток',
            ],
            'Honda CRF-450X Приморский край -Владивосток с пробегом' => [
                'Honda CRF-450X Приморский край -Владивосток с пробегом',
                'Honda CRF 450X Приморский край +с пробегом -Владивосток',
            ],
        ];
    }

    /**
     * @return array<string, string[]>
     */
    private static function createCarNegativeWords(): array
    {
        return [
            'Honda'          => ['-CRF', '-450X'],
            'Honda CRF'      => ['-450X'],
            'Honda CRF 450X' => [],
        ];
    }

    /**
     * @return array<int,TokenRuleInterface>
     */
    private static function createTokenRules(): array
    {
        return [
            new SignExtractionRule(),
            new EmptyCoreRule(),
            new NormalizedCoreRule(),
            new NegationRule(),
            new DuplicateFilterRule(),
            new PositiveFormattingRule(new PositiveTokenFormatter()),
        ];
    }

    /**
     * @return array<int, TokenCleanerInterface>
     */
    private static function createTokenCleaners(): array
    {
        return [
            new TokenCharacterFilter(),
            new TokenSignSplitter(),
        ];
    }

    /**
     * @return array<int, SentenceRuleInterface>
     */
    private static function createSentenceRules(): array
    {
        return [
            new CarNegativeWordRule(),
        ];
    }
}
