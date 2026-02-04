<?php

declare(strict_types=1);

namespace Service\Generator;

use App\Entity\KeyWord;
use App\Service\Generator\KeyWordCartesianProductGenerator;
use PHPUnit\Framework\TestCase;

final class KeyWordCartesianProductGeneratorTest extends TestCase
{
    /**
     * @dataProvider keywordSetsProvider
     */
    public function testGeneratorYieldsCombinations(array $sets): void
    {
        $keywordSets = array_map(
            static fn (array $words): array => array_map(
                static fn (string $word): KeyWord => new KeyWord($word),
                $words,
            ),
            $sets,
        );

        $generator = new KeyWordCartesianProductGenerator($keywordSets);
        $result    = \iterator_to_array($generator->generate(), false);

        $expected = [];
        foreach ($sets[0] as $first) {
            foreach ($sets[1] as $second) {
                foreach ($sets[2] as $third) {
                    $expected[] = "{$first} {$second} {$third}";
                }
            }
        }

        $this->assertSame($expected, $result);
    }

    public static function keywordSetsProvider(): array
    {
        return [
            [
                [
                    ['Honda', 'Honda CRF', 'Honda CRF-450X'],
                    ['Владивосток', 'Приморский край -Владивосток'],
                    ['продажа', 'покупка', 'цена', 'с пробегом'],
                ],
                [
                    ['Suzuki', 'Suzuki Jimny', 'Suzuki Jimny Siera', 'Suzuki Jimny Siera 2017'],
                    ['Владивосток', 'Приморский край -Владивосток'],
                    ['покупка', 'цена', 'с пробегом'],
                ],
            ],
        ];
    }
}
