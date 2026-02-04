<?php

declare(strict_types=1);

namespace Service;

use App\Entity\KeyWord;
use App\Service\Resolver\KeyWordConflictResolver;
use PHPUnit\Framework\TestCase;

final class KeyWordConflictResolverTest extends TestCase
{
    /**
     * @dataProvider keyWordCartProvider
     */
    public function testResolvesNestedPhrasesIntoMinusWords(array $keywords, array $expected): void
    {
        $resolver = new KeyWordConflictResolver();
        $result   = $resolver->resolve($keywords);

        self::assertSame($expected, $result);
    }

    /**
     * @return array<array{0: array<KeyWord>, 1: array<string, array<string>>}>
     */
    public static function keyWordCartProvider(): array
    {
        return [
            [
                [
                    new KeyWord('Honda'),
                    new KeyWord('Honda CRF'),
                    new KeyWord('Honda CRF-450X'),
                ],
                [
                    'Honda'          => ['-CRF', '-450X'],
                    'Honda CRF'      => ['-450X'],
                    'Honda CRF 450X' => [],
                ],
            ],
            [
                [
                    new KeyWord('Suzuki'),
                    new KeyWord('Suzuki Jimny'),
                    new KeyWord('Suzuki Jimny Siera'),
                    new KeyWord('Suzuki Jimny Siera 4l-84'),
                ],
                [
                    'Suzuki'                   => ['-Jimny', '-Siera', '-4l', '-84'],
                    'Suzuki Jimny'             => ['-Siera', '-4l', '-84'],
                    'Suzuki Jimny Siera'       => ['-4l', '-84'],
                    'Suzuki Jimny Siera 4l 84' => [],
                ],
            ],
        ];
    }
}
