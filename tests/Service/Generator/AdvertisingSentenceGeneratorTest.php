<?php

declare(strict_types=1);

namespace Service\Generator;

use App\Service\AdvertisingSentenceGeneratorService;
use App\Service\CorrectorAdvertising\CorrectorAdvertisingSentence;
use App\Service\CorrectorAdvertising\CorrectorAdvertisingSentenceFactory;
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
use App\Service\Parser\TextParser;
use App\Service\Resolver\KeyWordConflictResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class AdvertisingSentenceGeneratorTest extends TestCase
{
    public function testGenerateCreatesExpectedFile(): void
    {
        $facade = new CorrectorAdvertisingSentenceFactory(
            $this->createCorrectorContainer(),
            self::createSentenceRules()
        );
        $service = new AdvertisingSentenceGeneratorService(
            [new TextParser()],
            new KeyWordConflictResolver(),
            $facade
        );

        $projectRoot   = dirname(__DIR__, 3);
        $inputPath     = $projectRoot.'/tests/Command/files/test.txt';
        $expectedLines = file(
            $projectRoot.'/tests/Command/files/advertising-sentence.txt',
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        if ($expectedLines === false) {
            throw new \RuntimeException('Не удалось прочитать эталонный результат');
        }

        $outputDirectory = $this->createTemporaryDirectory();
        $outputPath      = $outputDirectory.'/nested/generated.txt';
        $extension       = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = 'txt';
        }

        $outputFile = $outputPath.'.'.$extension;

        try {
            $service->generate($inputPath, $outputPath);

            self::assertSame(
                $expectedLines,
                file($outputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            );
        } finally {
            $this->cleanupOutput($outputPath, $outputDirectory);
        }
    }

    private function createTemporaryDirectory(): string
    {
        $directory = sys_get_temp_dir().'/ad-service-output-'.bin2hex(random_bytes(6));

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Не удалось создать временную директорию');
        }

        return $directory;
    }

    private function cleanupOutput(string $outputPath, string $outputDirectory): void
    {
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }

        $nestedDirectory = dirname($outputPath);
        if (is_dir($nestedDirectory)) {
            rmdir($nestedDirectory);
        }

        if (is_dir($outputDirectory)) {
            rmdir($outputDirectory);
        }
    }

    private function createCorrectorContainer(): ContainerInterface
    {
        return new class(self::createTokenRules(), self::createTokenCleaners()) implements ContainerInterface {
            public function __construct(
                private readonly array $tokenRules,
                private readonly array $tokenCleaners,
            ) {
            }

            public function get(string $id): mixed
            {
                if ($id !== CorrectorAdvertisingSentence::class) {
                    throw new class(sprintf('Service "%s" not found.', $id)) extends \RuntimeException implements NotFoundExceptionInterface {
                    };
                }

                return new CorrectorAdvertisingSentence(
                    $this->tokenRules,
                    $this->tokenCleaners
                );
            }

            public function has(string $id): bool
            {
                return $id === CorrectorAdvertisingSentence::class;
            }
        };
    }

    /**
     * @return array<int, TokenRuleInterface>
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
