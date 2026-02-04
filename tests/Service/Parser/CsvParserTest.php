<?php

declare(strict_types=1);

namespace Service\Parser;

use App\Entity\KeyWord;
use App\Service\Parser\CsvParser;
use PHPUnit\Framework\TestCase;

final class CsvParserTest extends TestCase
{
    public function testParseHandlesCsvRows(): void
    {
        $input = <<<'CSV'
        "Honda","Honda CRF","Honda CRF-450X"
        Владивосток, Приморский край -Владивосток
        продажа, покупка, угон
        CSV;

        $parser = new CsvParser();
        $result = $parser->parse($input);

        $expectedWords = [
            ['Honda', 'Honda CRF', 'Honda CRF-450X'],
            ['Владивосток', 'Приморский край -Владивосток'],
            ['продажа', 'покупка', 'угон'],
        ];

        self::assertCount(3, $result);
        foreach ($expectedWords as $index => $words) {
            $this->assertArrayHasKey($index, $result);
            $actualWords = array_map(
                static fn (KeyWord $keyword): string => $keyword->getWord(),
                $result[$index]
            );
            $this->assertSame($words, $actualWords);
        }
    }
}
