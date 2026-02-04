<?php

declare(strict_types=1);

namespace Service\Parser;

use App\Entity\KeyWord;
use App\Service\Parser\TextParser;
use PHPUnit\Framework\TestCase;

final class TextParserTest extends TestCase
{
    public function testParseReturnsKeyWordSetsForCommonInput(): void
    {
        $input = <<<'TXT'
        Honda, Honda CRF, Honda CRF-450X
        Владивосток, Приморский край -Владивосток
        продажа, покупка, цена, с пробегом
        TXT;

        $parser = new TextParser();
        $result = $parser->parse($input);

        $expectedWords = [
            ['Honda', 'Honda CRF', 'Honda CRF-450X'],
            ['Владивосток', 'Приморский край -Владивосток'],
            ['продажа', 'покупка', 'цена', 'с пробегом'],
        ];

        $this->assertCount(3, $result);
        foreach ($expectedWords as $index => $words) {
            $this->assertArrayHasKey($index, $result);
            $actualWords = array_map(static fn (KeyWord $keyword): string => $keyword->getWord(), $result[$index]);
            $this->assertSame($words, $actualWords);
        }
    }

    public function testParseIgnoresBoundaryMarkers(): void
    {
        $input = <<<'TXT'
        начало>>>
        Honda, Honda CRF, Honda CRF-450X
        Владивосток, Приморский край -Владивосток
        продажа, покупка, цена, с пробегом
        <<< конец
        TXT;

        $parser = new TextParser();
        $result = $parser->parse($input);

        $this->assertCount(3, $result);
        $actualWords = array_map(static fn (KeyWord $keyword): string => $keyword->getWord(), $result[0]);
        $this->assertSame(['Honda', 'Honda CRF', 'Honda CRF-450X'], $actualWords);
    }
}
